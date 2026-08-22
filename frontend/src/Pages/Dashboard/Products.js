import { useEffect, useState } from "react";
import { Table, Button } from "react-bootstrap";
import { Axios } from "../../Api/axios";

export default function Products() {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    
    // 1. حالات إدارة الصفحات وعدد العناصر بالصفحة
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [limit, setLimit] = useState(10); // 🌟 عدد العناصر الافتراضي لكل صفحة

    // 2. طلب البيانات عند تغير رقم الصفحة (page)
    useEffect(() => {
        setLoading(true);
        Axios.get(`/products?page=${page}`)
            .then((res) => {
                setProducts(res.data?.data || []);
                setLastPage(res.data?.meta?.last_page || 1);
                
                // 🌟 قراءة عدد العناصر بالصفحة من الـ API ديناميكياً
                if (res.data?.meta?.per_page) {
                    setLimit(res.data.meta.per_page);
                }
                
                setLoading(false);
            })
            .catch((err) => {
                console.log(err);
                setProducts([]);
                setLoading(false);
            });
    }, [page]);

    // دالة لتنسيق الأزرار بنفس الألوان المطلوبة
    const getButtonStyle = (isDisabled) => ({
        backgroundColor: isDisabled ? "#9bb5f2" : "#1e56db",
        borderColor: isDisabled ? "#9bb5f2" : "#1e56db",
        color: "#ffffff",
        cursor: isDisabled ? "not-allowed" : "pointer"
    });

    return (
        <div className="bg-white w-100 p-2">
            <h1>Products Page</h1>

            <Table striped bordered hover responsive>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Category Id</th>
                    </tr>
                </thead>
                <tbody>
                    {loading ? (
                        <tr><td colSpan={5} className="text-center">Loading...</td></tr>
                    ) : products.length === 0 ? (
                        <tr><td colSpan={5} className="text-center">No Products Found.</td></tr>
                    ) : (
                        products.map((product, key) => (
                            <tr key={product?.id || key}>
                                {/* 🌟 الترقيم التراكمي: (رقم الصفحة - 1) * عدد العناصر + الفهرس + 1 */}
                                <td>{(page - 1) * limit + key + 1}</td>
                                <td>{product?.sku || "N/A"}</td>
                                <td>{product?.name || "N/A"}</td>
                                <td>{product?.categories?.[0]?.name || "N/A"}</td>
                                <td>{product?.categories?.[0]?.id || "N/A"}</td>
                            </tr>
                        ))
                    )}
                </tbody>
            </Table>

            {/* 3. أزرار التحكم بالصفحات بنفس الألوان */}
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