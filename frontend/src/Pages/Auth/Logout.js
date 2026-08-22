import { useEffect, useState } from "react";
import { Axios } from "../../Api/axios";
import { logout } from "../../Api/Api";
import Cookie from "cookie-universal";
import { DropdownButton, Dropdown } from "react-bootstrap";
import { useNavigate } from "react-router-dom";

export default function Logout() {
    const [name, setName] = useState("");
    const cookie = Cookie();
    const navigate = useNavigate();

    useEffect(() => {
        Axios.get('/user')
            .then((res) => {
                setName(res.data.first_name || res.data.email);
            })
            .catch((err) => {
                console.error("User fetch error:", err);
            });
    }, []);

    async function handleLogout() {
        try {
            await Axios.post('/logout');
            cookie.remove("e-commerce");
            window.location.pathname = "/login";
        } catch (err) {
            console.error("Logout error:", err);
        }
    }

    return (
        <DropdownButton 
            id="dropdown-basic-button" 
            title={name || "User"} 
            variant="primary"
            className="me-4"
        >
            <Dropdown.Item 
                onClick={handleLogout}
               className="logout-btn-text"
>
            
                Logout
            </Dropdown.Item>
        </DropdownButton>
    );
}