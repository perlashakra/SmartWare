import { useEffect, useState } from "react";
import { Table, Button } from "react-bootstrap";
import { Axios } from "../../Api/axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faPenToSquare, faTrash } from "@fortawesome/free-solid-svg-icons";
import { Link } from "react-router-dom";

export default function Categories() {
    // States
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [deleteCategory, setDeleteCategory] = useState(false);

    // 1. حالات إدارة الصفحات وعدد العناصر بالصفحة
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [limit, setLimit] = useState(10); // 🌟 عدد العناصر في الصفحة الواحدة

    // 2. جلب البيانات عند تغيير الصفحة أو حذف تصنيف
    useEffect(() => {
        setLoading(true);
        Axios.get(`/categories?page=${page}`)
            .then((res) => {
                setCategories(res.data?.data || []);
                setLastPage(res.data?.meta?.last_page || 1);
                
                // 🌟 تحديث عدد العناصر في الصفحة من الـ API إن وجد
                if (res.data?.meta?.per_page) {
                    setLimit(res.data.meta.per_page);
                }
                
                setLoading(false);
            })
            .catch((err) => {
                console.log(err);
                setCategories([]);
                setLoading(false);
            });
    }, [page, deleteCategory]);

    // Handle Delete Category
    async function handleDelete(id) {
        if (window.confirm("Are you sure you want to delete this category?")) {
            try {
                await Axios.delete(`/categories/${id}`);
                setDeleteCategory((prev) => !prev);
            } catch (err) {
                console.log(err);
            }
        }
    }

    // Mapping On Categories (🌟 حساب الترقيم التراكمي هنا)
    const categoriesShow = Array.isArray(categories) && categories.map((category, key) => (
        <tr key={category.id || key}>
            {/* 🌟 المعادلة التراكمية: (رقم الصفحة - 1) * عدد العناصر + الفهرس الحالي + 1 */}
            <td>{(page - 1) * limit + key + 1}</td>
            <td>{category.name || "N/A"}</td>
        </tr>
    ));

    // أسلوب التنسيق للأزرار
    const getButtonStyle = (isDisabled) => ({
        backgroundColor: isDisabled ? "#9bb5f2" : "#1e56db",
        borderColor: isDisabled ? "#9bb5f2" : "#1e56db",
        color: "#ffffff",
        cursor: isDisabled ? "not-allowed" : "pointer"
    });

    return (
        <div className="bg-white w-100 p-2">
            <h1>Categories Page</h1>
            <Table striped bordered hover responsive>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        {/* <th>Action</th> */}
                    </tr>
                </thead>
                <tbody>
                    {loading ? (
                        <tr>
                            <td colSpan={2} className="text-center">Loading...</td>
                        </tr>
                    ) : categories.length === 0 ? (
                        <tr>
                            <td colSpan={2} className="text-center">No Categories Found.</td>
                        </tr>
                    ) : (
                        categoriesShow
                    )}
                </tbody>
            </Table>

            {/* أزرار التحكم بالصفحات */}
            <div className="d-flex justify-content-center align-items-center gap-3 my-3">
                <Button 
                    style={getButtonStyle(page === 1)}
                    disabled={page === 1} 
                    onClick={() => setPage((prev) => prev - 1)}
                >
                    Previous
                </Button>

                <span>Page <strong>{page}</strong> of <strong>{lastPage}</strong></span>

                <Button 
                    style={getButtonStyle(page === lastPage)}
                    disabled={page === lastPage} 
                    onClick={() => setPage((prev) => prev + 1)}
                >
                    Next
                </Button>
            </div>
        </div>
    );
}