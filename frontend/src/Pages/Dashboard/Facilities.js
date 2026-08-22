import { useEffect, useState } from "react";
import { Table, Button, Badge, Spinner, Alert } from "react-bootstrap";
import { Axios } from "../../Api/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTrash } from "@fortawesome/free-solid-svg-icons";
import { Link } from "react-router-dom";

export default function Facilities() {
    const [facilities, setFacilities] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [activeTab, setActiveTab] = useState("all"); 
    const [alertMessage, setAlertMessage] = useState(null);

    const fetchFacilities = (page = 1, tab = activeTab) => {
        setLoading(true);

        let endpoint = `/facilities?page=${page}`;
        if (tab === "pending") {
            endpoint = `/admin/dashboard/facilities/pending?page=${page}`;
        }

        Axios.get(endpoint)
            .then((res) => {
                const responseData = res.data;

                const dataList = responseData?.data?.data || responseData?.data || responseData?.facilities?.data || responseData?.facilities || [];
                
                const pageCurrent = responseData?.meta?.current_page || responseData?.current_page || responseData?.facilities?.current_page || page;
                const pageLast = responseData?.meta?.last_page || responseData?.last_page || responseData?.facilities?.last_page || 1;
                const pagePerPage = responseData?.meta?.per_page || responseData?.per_page || responseData?.facilities?.per_page || 10;

                setFacilities(dataList);
                setCurrentPage(Number(pageCurrent));
                setLastPage(Number(pageLast));
                setPerPage(Number(pagePerPage));
                setLoading(false);
            })
            .catch((err) => {
                console.error("Error fetching facilities:", err);
                setFacilities([]);
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchFacilities(currentPage, activeTab);
    }, [currentPage, activeTab]);

    const handleTabChange = (tab) => {
        setActiveTab(tab);
        setCurrentPage(1);
    };

    async function handleDelete(id) {
        if (!id) {
            alert("Unable to delete: Facility ID is missing.");
            return;
        }

        if (window.confirm("Are you sure you want to delete this facility?")) {
            try {
                await Axios.delete(`/facilities/${id}`);
                setFacilities((prev) => prev.filter((item) => (item.id ?? item.facility_id) !== id));
            } catch (err) {
                console.error("Delete failed error details:", err.response);
                alert(err.response?.data?.message || "Failed to delete facility.");
            }
        }
    }

    const getButtonStyle = (isDisabled) => ({
        backgroundColor: isDisabled ? "#9bb5f2" : "#1e56db",
        borderColor: isDisabled ? "#9bb5f2" : "#1e56db",
        color: "#ffffff",
        cursor: isDisabled ? "not-allowed" : "pointer"
    });

    const renderStatusBadge = (status) => {
        const currentStatus = status || (activeTab === "pending" ? "pending" : "approved");

        if (currentStatus === "rejected") {
            return <Badge bg="danger">rejected</Badge>;
        } else if (currentStatus === "pending" || currentStatus === "incomplete") {
            return <Badge bg="warning" text="white">pending</Badge>;
        } else if (currentStatus === "approved" || currentStatus === "ready") {
            return <Badge bg="success">approved</Badge>;
        }
        return <Badge bg="secondary">{currentStatus}</Badge>;
    };

    const getFacilityName = (facility) => {
        const target = facility.facility || facility;
        return (
            target.facility_name_en ||
            target.facility_name_ar ||
            target.facility_name ||
            "N/A"
        );
    };

    // 🌟 التعديل هنا: قراءة اسم صاحب المنشأة مباشرة
    const getOwnerDisplay = (facility) => {
        const target = facility.facility || facility;
        if (target.owner && typeof target.owner === "object") {
            return target.owner.name || `${target.owner.first_name || ""} ${target.owner.last_name || ""}`.trim() || target.owner.email || "N/A";
        }
        return target.user_id ? `User ID: ${target.user_id}` : "N/A";
    };

    // 🌟 التعديل هنا: قراءة نص العنوان مباشرة
    const getAddressDisplay = (facility) => {
        const target = facility.facility || facility;
        if (target.address && typeof target.address === "object") {
            return target.address.name || target.address.address || "N/A";
        }
        return target.address_id ? `Address ID: ${target.address_id}` : "N/A";
    };

    return (
        <div className="bg-white w-100 p-3 rounded shadow-sm">
            {alertMessage && (
                <Alert variant={alertMessage.type} onClose={() => setAlertMessage(null)} dismissible>
                    {alertMessage.text}
                </Alert>
            )}

            <div className="d-flex border-bottom mb-3">
                <button
                    className={`btn rounded-0 border-0 py-2 px-4 ${
                        activeTab === "all" ? "fw-bold" : "text-muted"
                    }`}
                    style={{
                        borderBottom: activeTab === "all" ? "3px solid #1e56db" : "none",
                        color: activeTab === "all" ? "#1e56db" : "#6c757d"
                    }}
                    onClick={() => handleTabChange("all")}
                >
                    All Facilities
                </button>
                <button
                    className={`btn rounded-0 border-0 py-2 px-4 ${
                        activeTab === "pending" ? "fw-bold" : "text-muted"
                    }`}
                    style={{
                        borderBottom: activeTab === "pending" ? "3px solid #1e56db" : "none",
                        color: activeTab === "pending" ? "#1e56db" : "#6c757d"
                    }}
                    onClick={() => handleTabChange("pending")}
                >
                    Review Facilities
                </button>
            </div>

            <Table striped bordered hover responsive className="align-middle">
                <thead className="table-light">
                    <tr>
                        <th>#</th>
                        <th>Facility Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Owner</th>
                        <th>Address</th>
                        <th className="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {loading ? (
                        <tr>
                            <td colSpan={7} className="text-center py-4">
                                <Spinner animation="border" variant="primary" size="sm" className="me-2" />
                                Loading facilities...
                            </td>
                        </tr>
                    ) : facilities.length === 0 ? (
                        <tr>
                            <td colSpan={7} className="text-center py-4">
                                No Facilities Found.
                            </td>
                        </tr>
                    ) : (
                        facilities.map((item, key) => {
                            const facilityObj = item.facility || item;
                            const facilityId = item.facility_id || facilityObj.id;

                            return (
                                <tr key={facilityId || key}>
                                    <td>{key + 1 + (currentPage - 1) * perPage}</td>
                                    <td className="fw-bold">{getFacilityName(item)}</td>
                                    <td>{facilityObj.facility_type || "N/A"}</td>
                                    <td>{renderStatusBadge(facilityObj.facility_status)}</td>
                                    <td>{getOwnerDisplay(item)}</td>
                                    <td>{getAddressDisplay(item)}</td>
                                    <td className="text-center">
                                        {activeTab === "pending" ? (
                                            <Link
                                                to={`/dashboard/facilities/review/${facilityId}`}
                                                className="btn btn-outline-primary btn-sm"
                                            >
                                                Review
                                            </Link>
                                        ) : (
                                            <div className="d-flex justify-content-center align-items-center">
                                                <FontAwesomeIcon
                                                    onClick={() => handleDelete(facilityId)}
                                                    fontSize={"18px"}
                                                    color="red"
                                                    style={{ cursor: "pointer" }}
                                                    icon={faTrash}
                                                />
                                            </div>
                                        )}
                                    </td>
                                </tr>
                            );
                        })
                    )}
                </tbody>
            </Table>

            {!loading && lastPage > 1 && (
                <div className="d-flex justify-content-center align-items-center gap-3 my-3">
                    <Button
                        style={getButtonStyle(currentPage === 1)}
                        disabled={currentPage === 1}
                        onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                    >
                        Previous
                    </Button>

                    <span>
                        Page <strong>{currentPage}</strong> of <strong>{lastPage}</strong>
                    </span>

                    <Button
                        style={getButtonStyle(currentPage >= lastPage)}
                        disabled={currentPage >= lastPage}
                        onClick={() => setCurrentPage((prev) => Math.min(prev + 1, lastPage))}
                    >
                        Next
                    </Button>
                </div>
            )}
        </div>
    );
}