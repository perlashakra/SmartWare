import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import './bars.css';
import { faClipboardList,  } from '@fortawesome/free-solid-svg-icons';
import { faProductHunt } from '@fortawesome/free-brands-svg-icons';
import { 
  faWarehouse, 
  faFolderOpen, 
  
} from '@fortawesome/free-solid-svg-icons';
import { NavLink } from 'react-router-dom';
import { Menu } from '../../../Context/MenuContext';
import { useContext } from 'react';
import { WindowSize } from '../../../Context/WindowContext';

export default function SideBar() {
    const menu = useContext(Menu);
    const WindowContext = useContext(WindowSize);
    const windowSize = WindowContext.windowSize;
    const isOpen = menu.isOpen;
    
    return (
        <>
            <div
                style={{
                    position: "fixed",
                    top: "60px",
                    left: "0",
                    width: "100%",
                    height: "100vh",
                    backgroundColor: "rgba(0 , 0 , 0 , 0.2)",
                    display: windowSize < 768 && isOpen ? 'block' : 'none',
                    zIndex: 9
                }}
            >
            </div>

            <div className={`side-bar pt-3 ${isOpen ? 'sidebar-open' : 'sidebar-closed'}`}
                style={{
                    position: "fixed", /* ✅ أصبح fixed دائماً لضمان عدم التحرك أثناء السكرول */
                    top: "60px",
                    left: windowSize < 768 ? (isOpen ? 0 : "-100%") : 0,
                    width: isOpen ? "240px" : "65px",
                    height: "calc(100vh - 60px)",
                    zIndex: 10
                }} 
            >
                <NavLink to="/dashboard/categories" className="d-flex align-items-center gap-2 side-bar-link">
                    <FontAwesomeIcon
                        style={{ padding: isOpen ? "10px 8px 10px 15px" : "10px 13px" }}
                        icon={faFolderOpen} 
                    />
                    <p className='m-0' style={{ display: isOpen ? "block" : "none" }}>
                        Categories
                    </p>
                </NavLink>
            
                <NavLink to="/dashboard/products" className="d-flex align-items-center gap-2 side-bar-link">
                    <FontAwesomeIcon
                        style={{ padding: isOpen ? "10px 8px 10px 15px" : "10px 13px" }}
                        icon={faProductHunt}
                    />
                    <p className='m-0' style={{ display: isOpen ? "block" : "none" }}>
                        Products
                    </p>
                </NavLink>

                <NavLink to="/dashboard/facilities" className="d-flex align-items-center gap-2 side-bar-link">
                    <FontAwesomeIcon
                        style={{ padding: isOpen ? "10px 8px 10px 15px" : "10px 13px" }}
                        icon={faWarehouse}
                    />
                    <p className='m-0' style={{ display: isOpen ? "block" : "none" }}>
                        Facility Requests
                    </p>
                </NavLink>

                <NavLink 
                    to="/dashboard/admin-requests" 
                    className="d-flex align-items-center gap-2 side-bar-link"
                >
                    <FontAwesomeIcon
                        style={{ padding: isOpen ? "10px 8px 10px 15px" : "10px 13px" }}
                        icon={faClipboardList}
                    />
                    <p className='m-0' style={{ display: isOpen ? "block" : "none" }}>
                        Admin Requests
                    </p>
                </NavLink>
            </div>
        </>
    );
}