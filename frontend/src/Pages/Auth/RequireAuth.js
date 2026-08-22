import Cookie from "cookie-universal";
import { useEffect, useState } from "react";
import { Outlet, Navigate, useNavigate } from "react-router-dom";
import Loading from "../../Components/Loading/Loading";
import { Axios } from "../../Api/axios";
import Err403 from "./403";

export default function RequireAuth({ allowedRole = [] }) {
    const [user, setUser] = useState("");
    const [loading, setLoading] = useState(true);
    const cookie = Cookie();
    const token = cookie.get("e-commerce");
    const navigate = useNavigate();

    useEffect(() => {
        let isMounted = true;
        if (token) {
            Axios.get(`/user`)
                .then((data) => {
                    if (isMounted) {
                        setUser(data.data);
                        setLoading(false);
                    }
                })
                .catch(() => {
                    if (isMounted) setLoading(false);
                });
        } else {
            setLoading(false);
        }

        return () => { isMounted = false; };
    }, [token]);

    // 1. عدم وجود توكين -> تحويل للوجن
    if (!token) {
        return <Navigate to="/login" replace={true} />;
    }

    // 2. أثناء التحميل
    if (loading) {
        return <Loading />;
    }

    // 3. إذا لم يملك صلاحية -> نقوم بتحويله فوراً للمسار الرئيسي بـ replace
    // هذا يمسح محاولة دخل الداشبورد من الـ History كلياً!
    if (!allowedRole?.includes(user?.role)) {
        return <Navigate to="/403" replace={true} />;
    }

    return <Outlet />;
}