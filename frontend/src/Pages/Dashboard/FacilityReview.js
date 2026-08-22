import { useEffect, useState } from "react";
import { Table, Button, Badge, Spinner, Alert, Card, Form, Row, Col } from "react-bootstrap";
import { Axios } from "../../Api/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowLeft } from "@fortawesome/free-solid-svg-icons";
import { useParams, useNavigate } from "react-router-dom";

export default function FacilityReview() {
    const { id } = useParams();
    const navigate = useNavigate();

    const [facilityData, setFacilityData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    
    const [decision, setDecision] = useState("approve");
    const [rejectionReason, setRejectionReason] = useState("");
    const [alertMessage, setAlertMessage] = useState(null);

    useEffect(() => {
        setLoading(true);
        Axios.get(`/admin/dashboard/facilities/${id}`)
            .then((res) => {
                const data = res.data?.facility ? res.data.facility : res.data;
                setFacilityData(data);
                setLoading(false);
            })
            .catch((err) => {
                console.error("Error fetching facility details:", err);
                setAlertMessage({ type: "danger", text: "Failed to load facility details." });
                setLoading(false);
            });
    }, [id]);

    const handleSubmitDecision = async (e) => {
        e.preventDefault();

        if (decision === "reject" && !rejectionReason.trim()) {
            alert("Please provide a reason for rejection.");
            return;
        }

        setSubmitting(true);
        try {
            const payload = {
                action: decision, // "approve" أو "reject"
                ...(decision === "reject" && { rejection_reason: rejectionReason })
            };

            await Axios.post(`/admin/dashboard/facilities/${id}/review`, payload);
            
            setAlertMessage({
                type: decision === "approve" ? "success" : "warning",
                text: `Facility decision saved successfully as ${decision}.`
            });

            setTimeout(() => navigate("/dashboard/facilities"), 1500);
        } catch (err) {
            console.error("Decision submission error:", err);
            setAlertMessage({
                type: "danger",
                text: err.response?.data?.message || "Failed to submit decision."
            });
        } finally {
            setSubmitting(false);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return "N/A";
        return new Date(dateString).toISOString().split("T")[0];
    };

    if (loading) {
        return (
            <div className="text-center py-5">
                <Spinner animation="border" variant="primary" />
                <p className="mt-2">Loading facility review details...</p>
            </div>
        );
    }

    if (!facilityData) {
        return (
            <Alert variant="danger" className="m-3">
                Facility not found or error loading data.
            </Alert>
        );
    }

    const owner = facilityData.owner || {};
    const address = facilityData.address || {};
    const status = facilityData.facility_status || "pending";

    return (
        <div className="bg-white p-4 rounded shadow-sm">
            <div className="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <Button variant="outline-secondary" size="sm" onClick={() => navigate(-1)}>
                    <FontAwesomeIcon icon={faArrowLeft} className="me-2" /> Back
                </Button>
                <h4 className="m-0 fw-bold text-primary">Review Facility Request #{id}</h4>
            </div>

            {alertMessage && (
                <Alert variant={alertMessage.type} onClose={() => setAlertMessage(null)} dismissible>
                    {alertMessage.text}
                </Alert>
            )}

            <Row className="mb-4">
                <Col md={6}>
                    <Card className="h-100 border-0 bg-light">
                        <Card.Body>
                            <Card.Title className="fw-bold border-bottom pb-2 fs-6 text-muted">Facility Details</Card.Title>
                            <p className="mb-2"><strong>Name:</strong> {facilityData.facility_name_en || facilityData.facility_name_ar || "N/A"}</p>
                            <p className="mb-2"><strong>Type:</strong> {facilityData.facility_type || "N/A"}</p>
                            <p className="mb-2"><strong>Business Type:</strong> {facilityData.business_type || "N/A"}</p>
                            
                            {/* ✅ تفعيل خلفية أحمر فاتح للـ rejected */}
                            <p className="mb-2">
                                <strong>Status:</strong>{" "}
                                {status === "rejected" ? (
                                    <Badge style={{ backgroundColor: "#fde8e8", color: "#e53e3e", border: "1px solid #f8b4b4" }}>
                                        {status}
                                    </Badge>
                                ) : status === "approved" ? (
                                    <Badge bg="success">{status}</Badge>
                                ) : (
                                    <Badge bg="warning" text="dark">{status}</Badge>
                                )}
                            </p>
                            
                            <p className="mb-0"><strong>Address:</strong> {address.address || "N/A"}</p>
                        </Card.Body>
                    </Card>
                </Col>

                <Col md={6}>
                    <Card className="h-100 border-0 bg-light">
                        <Card.Body>
                            <Card.Title className="fw-bold border-bottom pb-2 fs-6 text-muted">Owner Details</Card.Title>
                            <p className="mb-2"><strong>Name:</strong> {`${owner.first_name || ""} ${owner.last_name || ""}`.trim() || "N/A"}</p>
                            <p className="mb-2"><strong>Email:</strong> {owner.email || "N/A"}</p>
                            <p className="mb-0"><strong>Phone:</strong> {owner.phone_number || "N/A"}</p>
                        </Card.Body>
                    </Card>
                </Col>
            </Row>

            <Card className="border-0 bg-light p-3">
                <Card.Body>
                    <h5 className="fw-bold mb-3 text-secondary">Decision</h5>
                    <Form onSubmit={handleSubmitDecision}>
                        <div className="d-flex gap-4 mb-3">
                            <Form.Check
                                type="radio"
                                id="approve-radio"
                                label="Approve"
                                name="decision"
                                value="approve"
                                checked={decision === "approve"}
                                onChange={() => setDecision("approve")}
                            />
                            <Form.Check
                                type="radio"
                                id="reject-radio"
                                label="Reject"
                                name="decision"
                                value="reject"
                                checked={decision === "reject"}
                                onChange={() => setDecision("reject")}
                            />
                        </div>

                        <Row className="mb-3">
                            <Col md={4}>
                                <Form.Group controlId="documentView">
                                    <Form.Label className="small fw-semibold text-muted">Document</Form.Label>
                                    {facilityData.document ? (
                                        <div>
                                            <a
                                                href={facilityData.document}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="btn btn-sm btn-outline-primary w-100 text-truncate"
                                            >
                                                View Attached Document
                                            </a>
                                        </div>
                                    ) : (
                                        <Form.Control type="text" value="No document attached" readOnly disabled />
                                    )}
                                </Form.Group>
                            </Col>

                            <Col md={4}>
                                <Form.Group controlId="startDateView">
                                    <Form.Label className="small fw-semibold text-muted">Start Date</Form.Label>
                                    <Form.Control
                                        type="text"
                                        value={formatDate(facilityData.created_at)}
                                        readOnly
                                        disabled
                                    />
                                </Form.Group>
                            </Col>

                            <Col md={4}>
                                <Form.Group controlId="expirationDateView">
                                    <Form.Label className="small fw-semibold text-muted">Expiration Date</Form.Label>
                                    <Form.Control
                                        type="text"
                                        value={formatDate(facilityData.updated_at)}
                                        readOnly
                                        disabled
                                    />
                                </Form.Group>
                            </Col>
                        </Row>

                        {decision === "reject" && (
                            <Row className="mb-3">
                                <Col md={12}>
                                    <Form.Group controlId="rejectionReason">
                                        <Form.Label className="fw-semibold text-danger">Rejection Reason *</Form.Label>
                                        <Form.Control
                                            as="textarea"
                                            rows={2}
                                            placeholder="Enter rejection reason..."
                                            value={rejectionReason}
                                            onChange={(e) => setRejectionReason(e.target.value)}
                                        />
                                    </Form.Group>
                                </Col>
                            </Row>
                        )}

                        <Button
                            type="submit"
                            variant={decision === "approve" ? "success" : "danger"}
                            disabled={submitting}
                            className="mt-2"
                        >
                            {submitting ? "Submitting..." : decision === "approve" ? "Submit Approval" : "Submit Rejection"}
                        </Button>
                    </Form>
                </Card.Body>
            </Card>
        </div>
    );
}

