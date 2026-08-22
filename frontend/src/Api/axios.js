import axios from "axios";
import { baseURL } from "./Api";
import Cookie from "cookie-universal";

export const Axios = axios.create({
  baseURL: baseURL,
  headers: {
    'Accept': 'application/json', // يرسل تلقائياً مع كل طلب
  },
});

Axios.interceptors.request.use((config) => {
  const cookie = Cookie();
  const token = cookie.get("e-commerce"); //  يقرأ الكوكيز في اللحظة التي يخرج فيها الطلب تماماً!
console.log("Current Token Sent:", token);
  if (token) {
    config.headers.Authorization = `Bearer ${token}`; //  يرفق أحدث توكن موجود حالياً
  }

  return config;
});