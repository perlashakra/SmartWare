import { useEffect, useState } from "react";
import { Table, Button, Modal, Form, Badge, Spinner, Alert } from "react-bootstrap";
import { Axios } from "../../Api/axios";

export default function Admin() {
    const [requests, setRequests] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState("pending");

    // Pagination
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);

    // Feedback message
    const [alertMessage, setAlertMessage] = useState(null);

    // Modals
    const [showCreateModal, setShowCreateModal] = useState(false);
    
    const [newAdmin, setNewAdmin] = useState({
        first_name: "",
        last_name: "",
        email: "",
        phone_number: "",
        password: "",
        language_preference: "ar"
    });

    const [selectedRequest, setSelectedRequest] = useState(null);
    const [requestDetails, setRequestDetails] = useState(null);
    const [loadingDetails, setLoadingDetails] = useState(false);
    const [showReviewModal, setShowReviewModal] = useState(false);
    const [reviewStatus, setReviewStatus] = useState("approve");
    const [rejectionReason, setRejectionReason] = useState("");

    const fetchRequests = () => {
        setLoading(true);
        const endpoint = activeTab === "pending"
            ? `/admin/dashboard/requests/pending?page=${page}`
            : `/admin/dashboard/requests/complete?page=${page}`;

        Axios.get(endpoint)
            .then((res) => {
                setRequests(res.data?.data || []);
                setLastPage(res.data?.last_page || 1);
                setLoading(false);
            })
            .catch((err) => {
                console.error(err);
                setRequests([]);
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchRequests();
    }, [activeTab, page]);

    const handleOpenReviewModal = (req) => {
        setSelectedRequest(req);
        setShowReviewModal(true);
        setLoadingDetails(true);

        Axios.get(`/admin/dashboard/requests/${req.id}`)
            .then((res) => {
                setRequestDetails(res.data?.user || res.data);
                setLoadingDetails(false);
            })
            .catch((err) => {
                console.error(err);
                setLoadingDetails(false);
            });
    };

    const handleCreateAdminSubmit = (e) => {
        e.preventDefault();
        Axios.post("/admin/dashboard/createAdmin", newAdmin)
            .then(() => {
                setShowCreateModal(false);
                setNewAdmin({
                    first_name: "",
                    last_name: "",
                    email: "",
                    phone_number: "",
                    password: "",
                    language_preference: "ar"
                });
                setAlertMessage({ type: "success", text: "New admin created successfully!" });
                fetchRequests();
            })
            .catch((err) => {
                console.error(err);
                alert(err.response?.data?.message || "Error creating admin");
            });
    };

    const handleReviewSubmit = (e) => {
        e.preventDefault();
        if (!selectedRequest) return;

        const payload = {
            action: reviewStatus,
            ...(reviewStatus === "reject" && { rejection_reason: rejectionReason }),
        };

        Axios.post(`/admin/dashboard/requests/${selectedRequest.id}/review`, payload)
            .then(() => {
                handleCloseReviewModal();
                if (reviewStatus === "approve") {
                    setAlertMessage({
                        type: "success",
                        text: `User #${selectedRequest.id} approved successfully and moved to Approved Accounts.`
                    });
                } else {
                    setAlertMessage({
                        type: "warning",
                        text: `Request #${selectedRequest.id} rejected.`
                    });
                }
                fetchRequests();
            })
            .catch((err) => {
                console.error(err);
                alert(err.response?.data?.message || "Error submitting review");
            });
    };

    const handleCloseReviewModal = () => {
        setShowReviewModal(false);
        setSelectedRequest(null);
        setRequestDetails(null);
        setRejectionReason("");
        setReviewStatus("approve");
    };

    return (
        <div className="bg-white w-100 p-3">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h1 className="m-0" style={{ fontSize: "36px", fontWeight: "normal" }}>
                    Admin Requests Page
                </h1>
                <Button
                    style={{ backgroundColor: "#1e56db", borderColor: "#1e56db" }}
                    onClick={() => setShowCreateModal(true)}
                >
                    + Create New Admin
                </Button>
            </div>

            {/* Alert Message */}
            {alertMessage && (
                <Alert variant={alertMessage.type} onClose={() => setAlertMessage(null)} dismissible>
                    {alertMessage.text}
                </Alert>
            )}

            {/* Tabs */}
            <div className="d-flex border-bottom mb-3">
                <button
                    className={`btn rounded-0 border-0 py-2 px-4 ${activeTab === "pending" ? "border-bottom text-primary fw-bold" : "text-muted"}`}
                    style={{
                        borderBottom: activeTab === "pending" ? "3px solid #1e56db" : "none",
                        color: activeTab === "pending" ? "#1e56db" : "#6c757d"
                    }}
                    onClick={() => { setActiveTab("pending"); setPage(1); }}
                >
                    Incomplete Onboarding
                </button>
                <button
                    className={`btn rounded-0 border-0 py-2 px-4 ${activeTab === "complete" ? "border-bottom text-primary fw-bold" : "text-muted"}`}
                    style={{
                        borderBottom: activeTab === "complete" ? "3px solid #1e56db" : "none",
                        color: activeTab === "complete" ? "#1e56db" : "#6c757d"
                    }}
                    onClick={() => { setActiveTab("complete"); setPage(1); }}
                >
                    Ready for Review
                </button>
            </div>

            <Table striped bordered hover responsive>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Role</th>
                        <th>Onboarding Status</th>
                        {/* يظهر عمود الـ Action فقط إذا لم تكن في تبويب Incomplete */}
                        {activeTab !== "pending" && <th>Action</th>}
                    </tr>
                </thead>
                <tbody>
                    {loading ? (
                        <tr><td colSpan={activeTab === "pending" ? 6 : 7} className="text-center py-4">Loading...</td></tr>
                    ) : requests.length === 0 ? (
                        <tr><td colSpan={activeTab === "pending" ? 6 : 7} className="text-center py-4">No requests found in this tab.</td></tr>
                    ) : (
                        requests.map((req, index) => (
                            <tr key={req.id || index}>
                                <td>{(page - 1) * 15 + (index + 1)}</td>
                                <td>{`${req.first_name || ''} ${req.last_name || ''}`}</td>
                                <td>{req.email || "N/A"}</td>
                                <td>{req.phone_number || "N/A"}</td>
                                <td>
                                    <Badge bg="info">
                                        {req.role || "N/A"}
                                    </Badge>
                                </td>
                                <td>
                                    <Badge bg={activeTab === "pending" ? "warning" : "success"}>
                                        {activeTab === "pending" ? "Incomplete" : "Completed"}
                                    </Badge>
                                </td>
                                {/* خلية الـ Action تظهر فقط في تبويب Ready for Review */}
                                {activeTab !== "pending" && (
                                    <td>
                                        <Button
                                            variant="outline-primary"
                                            size="sm"
                                            onClick={() => handleOpenReviewModal(req)}
                                        >
                                            Review
                                        </Button>
                                    </td>
                                )}
                            </tr>
                        ))
                    )}
                </tbody>
            </Table>

            {/* Pagination */}
            <div className="d-flex justify-content-center align-items-center gap-3 my-3">
                <Button
                    className="border-0"
                    style={{
                        backgroundColor: page === 1 ? "#c2d4f8" : "#1e56db",
                        color: "#fff",
                        cursor: page === 1 ? "not-allowed" : "pointer"
                    }}
                    disabled={page === 1}
                    onClick={() => setPage((prev) => prev - 1)}
                >
                    Previous
                </Button>

                <span>Page <strong>{page}</strong> of <strong>{lastPage}</strong></span>

                <Button
                    className="border-0"
                    style={{
                        backgroundColor: page === lastPage ? "#c2d4f8" : "#1e56db",
                        color: "#fff",
                        cursor: page === lastPage ? "not-allowed" : "pointer"
                    }}
                    disabled={page === lastPage}
                    onClick={() => setPage((prev) => prev + 1)}
                >
                    Next
                </Button>
            </div>

            {/* Modal: Create Admin */}
            <Modal show={showCreateModal} onHide={() => setShowCreateModal(false)} centered>
                <Modal.Header closeButton>
                    <Modal.Title>Create New Admin</Modal.Title>
                </Modal.Header>
                <Form onSubmit={handleCreateAdminSubmit}>
                    <Modal.Body>
                        <div className="row">
                            <div className="col-md-6">
                                <Form.Group className="mb-3">
                                    <Form.Label>First Name</Form.Label>
                                    <Form.Control
                                        type="text"
                                        required
                                        value={newAdmin.first_name}
                                        onChange={(e) => setNewAdmin({ ...newAdmin, first_name: e.target.value })}
                                    />
                                </Form.Group>
                            </div>
                            <div className="col-md-6">
                                <Form.Group className="mb-3">
                                    <Form.Label>Last Name</Form.Label>
                                    <Form.Control
                                        type="text"
                                        required
                                        value={newAdmin.last_name}
                                        onChange={(e) => setNewAdmin({ ...newAdmin, last_name: e.target.value })}
                                    />
                                </Form.Group>
                            </div>
                        </div>

                        <Form.Group className="mb-3">
                            <Form.Label>Email</Form.Label>
                            <Form.Control
                                type="email"
                                required
                                value={newAdmin.email}
                                onChange={(e) => setNewAdmin({ ...newAdmin, email: e.target.value })}
                            />
                        </Form.Group>

                        <Form.Group className="mb-3">
                            <Form.Label>Phone Number</Form.Label>
                            <Form.Control
                                type="text"
                                required
                                placeholder="0933449620"
                                value={newAdmin.phone_number}
                                onChange={(e) => setNewAdmin({ ...newAdmin, phone_number: e.target.value })}
                            />
                        </Form.Group>

                        <Form.Group className="mb-3">
                            <Form.Label>Password</Form.Label>
                            <Form.Control
                                type="password"
                                required
                                value={newAdmin.password}
                                onChange={(e) => setNewAdmin({ ...newAdmin, password: e.target.value })}
                            />
                        </Form.Group>

                        <Form.Group className="mb-3">
                            <Form.Label>Language Preference</Form.Label>
                            <Form.Select
                                value={newAdmin.language_preference}
                                onChange={(e) => setNewAdmin({ ...newAdmin, language_preference: e.target.value })}
                            >
                                <option value="ar">Arabic (ar)</option>
                                <option value="en">English (en)</option>
                            </Form.Select>
                        </Form.Group>
                    </Modal.Body>
                    <Modal.Footer>
                        <Button variant="secondary" onClick={() => setShowCreateModal(false)}>Cancel</Button>
                        <Button style={{ backgroundColor: "#1e56db", borderColor: "#1e56db" }} type="submit">Create</Button>
                    </Modal.Footer>
                </Form>
            </Modal>

            {/* Modal: Review / Details Request */}
            <Modal show={showReviewModal} onHide={handleCloseReviewModal} centered size="lg">
                <Modal.Header closeButton>
                    <Modal.Title>
                        Review Request #{selectedRequest?.id}
                    </Modal.Title>
                </Modal.Header>
                <Form onSubmit={handleReviewSubmit}>
                    <Modal.Body>
                        {loadingDetails ? (
                            <div className="text-center py-4">
                                <Spinner animation="border" variant="primary" />
                            </div>
                        ) : (
                            <>
                                <div className="mb-4 p-3 bg-light rounded border">
                                    <div className="mb-3">
                                        <Badge bg="success">
                                            Onboarding Completed - Ready for Approval
                                        </Badge>
                                    </div>
                                    <p className="mb-2"><strong>Name:</strong> {requestDetails?.first_name} {requestDetails?.last_name}</p>
                                    <p className="mb-2"><strong>Email:</strong> {requestDetails?.email}</p>
                                    <p className="mb-2"><strong>Phone:</strong> {requestDetails?.phone_number}</p>
                                    <p className="mb-2"><strong>Role:</strong> {requestDetails?.role}</p>

                                    <div className="mt-3 pt-3 border-top d-flex flex-column gap-2">
                                        <div className="d-flex align-items-center gap-2">
                                            <strong>ID Document:</strong>
                                            {requestDetails?.document?.file_url ? (
                                                <a
                                                    href={requestDetails.document.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="btn btn-sm btn-outline-primary"
                                                >
                                                    📄 View ID Document
                                                </a>
                                            ) : (
                                                <span className="text-muted small">Not Uploaded</span>
                                            )}
                                        </div>

                                        <div className="mt-2">
                                            <strong>Facility Legal Documents:</strong>
                                            {requestDetails?.facilities && requestDetails.facilities.length > 0 ? (
                                                <ul className="list-unstyled mt-1 mb-0 ps-2">
                                                    {requestDetails.facilities.map((facility, idx) => (
                                                        <li key={facility.id || idx} className="mb-1 d-flex align-items-center gap-2">
                                                            <span className="small">• {facility.facility_name_en || facility.facility_name_ar}:</span>
                                                            {facility.document?.file_url ? (
                                                                <a
                                                                    href={facility.document.file_url}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                    className="btn btn-sm btn-outline-info py-0 px-2"
                                                                    style={{ fontSize: "12px" }}
                                                                >
                                                                    🏢 View Document
                                                                </a>
                                                            ) : (
                                                                <span className="text-muted small">No Document Uploaded</span>
                                                            )}
                                                        </li>
                                                    ))}
                                                </ul>
                                            ) : (
                                                <span className="text-muted small ms-2">No facilities registered</span>
                                            )}
                                        </div>

                                    </div>
                                </div>

                                <Form.Group className="mb-3">
                                    <Form.Label>Status Action</Form.Label>
                                    <Form.Select
                                        value={reviewStatus}
                                        onChange={(e) => setReviewStatus(e.target.value)}
                                    >
                                        <option value="approve">Approve (Move to Approved Accounts)</option>
                                        <option value="reject">Reject Request</option>
                                    </Form.Select>
                                </Form.Group>

                                {reviewStatus === "reject" && (
                                    <Form.Group className="mb-3">
                                        <Form.Label>Rejection Reason</Form.Label>
                                        <Form.Control
                                            as="textarea"
                                            rows={3}
                                            required
                                            placeholder="Enter rejection reason..."
                                            value={rejectionReason}
                                            onChange={(e) => setRejectionReason(e.target.value)}
                                        />
                                    </Form.Group>
                                )}
                            </>
                        )}
                    </Modal.Body>
                    <Modal.Footer>
                        <Button variant="secondary" onClick={handleCloseReviewModal}>
                            Close
                        </Button>
                        <Button variant="success" type="submit" disabled={loadingDetails}>
                            Submit Review
                        </Button>
                    </Modal.Footer>
                </Form>
            </Modal>
        </div>
    );
}