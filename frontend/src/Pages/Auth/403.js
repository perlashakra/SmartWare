import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import "./403.css";

export default function Err403() {
    const navigate = useNavigate();

    useEffect(() => {
        // استبدال الحالة الحالية في سجل المتصفح بالصفحة الرئيسية
        // حتى يتغير المسار السابق المحفوظ في سهم الرجوع إلى "/"
        window.history.pushState(null, "", "/");
    }, []);

    return (
        <div className="text-wrapper">
            <div className="title">403 - ACCESS DENIED</div>
            <div className="subtitle">
                Oops, You don't have permission to access this page.
            </div>
            
            {/* إمكانية إضافة زر إضافي للتحويل المباشر للهوم */}
            <button 
                onClick={() => navigate('/', { replace: true })} 
                style={{
                    marginTop: '20px',
                    padding: '10px 20px',
                    backgroundColor: '#1e56db',
                    color: 'white',
                    border: 'none',
                    borderRadius: '5px',
                    cursor: 'pointer',
                    fontWeight: 'bold'
                }}
            >
                Go to Home
            </button>
        </div>
    );
}