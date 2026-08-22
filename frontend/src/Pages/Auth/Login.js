import { useState } from "react";
import { baseURL, login } from "../../Api/Api";
import LoadingSubmit from "../../Components/Loading/Loading";
import Cookie from "cookie-universal";
import Form from 'react-bootstrap/Form';
import { useNavigate } from "react-router-dom";
import { Axios } from "../../Api/axios";

export default function Login() {
    // State
    const [form, setForm] = useState({
        email: "",
        password: "",
    });
    const navigate = useNavigate();
    
    // Loading
    const [loading, setLoading] = useState(false);
    
    // Cookies
    const cookie = Cookie();
    
    // Err
    const [err, setErr] = useState("");

    // Handle Form Change
    function handleChange(e) {
        setForm({ ...form, [e.target.name]: e.target.value });
    }

    // Handle Submit
    async function handleSubmit(e) {
        e.preventDefault();
        setLoading(true);
        setErr(""); // إعادة إعادة تعيين الخطأ عند كل محاولة

        try {
            // ملاحظة: إذا كان Axios يحتوي مسبقاً على baseURL، يكفي تمرير `/${login}` أو `login`
            const res = await Axios.post(`${baseURL}/${login}`, {
                login: form.email,
                password: form.password,
            });

            setLoading(false);
            const token = res.data.token;
            
            cookie.set("e-commerce", token);
            navigate('/dashboard', { replace: true });

        } catch (err) {
            setLoading(false);
            
            // الفحص الآمن باستخدام Optional Chaining (?.) لمنع الـ Uncaught Error
            if (err.response?.status === 401) {
                setErr("Wrong - Email or Password");  
            } else if (err.response?.status === 422) {
                setErr("Please check the entered data.");
            } else {
                setErr("Internal Server ERR or Network Issue");
            }
        }
    }

    return (
        <>
            {loading && <LoadingSubmit />}
            <div dir="ltr" className="container">
                <div className="row" style={{ height: "100vh" }}>
                    <Form className="login-form" onSubmit={handleSubmit}>
                        <div className="custom-form">
                            <h6 style={{ color: "#64748B" }}>welcome back</h6>
                            <h3>Login</h3>

                            <Form.Group className="form-custom" controlId="exampleForm.ControlInput1">
                                <Form.Control 
                                    type="email" 
                                    value={form.email}
                                    onChange={handleChange}
                                    name="email"
                                    placeholder="Enter Your Email.."
                                    required 
                                />
                                <Form.Label>Email</Form.Label>
                            </Form.Group>

                            <Form.Group className="form-custom" controlId="exampleForm.ControlInput2">
                                <Form.Control 
                                    type="password" 
                                    value={form.password}
                                    onChange={handleChange}
                                    name="password"
                                    placeholder="Enter Your Password.."
                                    minLength="6"
                                    required 
                                />
                                <Form.Label>Password</Form.Label>
                            </Form.Group>

                            <button className="login-btn">Login</button>
                            {err !== "" && <span className="error d-block mt-2 text-danger">{err}</span>}
                        </div>
                    </Form>
                </div>
            </div>
        </>
    );
}