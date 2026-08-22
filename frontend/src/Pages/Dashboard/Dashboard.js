import { Outlet } from "react-router-dom";
import SideBar from "../../Css/components/Dashboard/SideBar";
import TopBar from "../../Css/components/Dashboard/TopBar";
import { useContext } from "react";
import { Menu } from "../../Context/MenuContext";
import { WindowSize } from "../../Context/WindowContext";

export default function Dashboard() {
    const menu = useContext(Menu);
    const WindowContext = useContext(WindowSize);
    
    const isOpen = menu.isOpen;
    const windowSize = WindowContext.windowSize;

    // حساب الإزاحة حسب الشاشة وحالة القائمة
    const getMarginLeft = () => {
        if (windowSize < 768) return "0px";
        return isOpen ? "240px" : "65px";
    };

    return (
        <div className="position-relative dashboard">
            <TopBar />
            <div style={{ marginTop: "60px" }} className="d-flex">
                <SideBar />
                
                {/* الحاوية الخاصة بالمحتوى لمنع التداخل مع السايد بار */}
                <div 
                    style={{
                        marginLeft: getMarginLeft(),
                        width: "100%",
                        padding: "20px",
                        transition: "margin-left 0.3s ease"
                    }}
                >
                    <Outlet />
                </div>
            </div>
        </div>
    );
}