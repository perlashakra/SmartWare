import React, { useState } from "react";
import emailjs from "@emailjs/browser";
import { useTranslation } from "react-i18next";
import contactSvg from "./images/Contact.svg";

export default function Contact() {
  const { t } = useTranslation();

  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    message: "",
  });

  const [status, setStatus] = useState("");

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    if (status) setStatus("");
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setStatus(t('contact.sending') || "جاري إرسال الطلب...");

    emailjs
      .send(
        "service_smartware",
        "template_3lbk183",
        {
          from_name: formData.name,
          from_email: formData.email,
          phone: formData.phone,
          message: formData.message,
        },
        "qiqTHFKMtwF8Bx_AS"
      )
      .then(
        () => {
          setStatus(t('contact.successMessage') || "تم إرسال الطلب بنجاح! سنرد عليكِ في أقرب وقت.");
          setFormData({ name: "", email: "", phone: "", message: "" });

          setTimeout(() => {
            setStatus("");
          }, 2000);
        },
        (error) => {
          setStatus(t('contact.errorMessage') || "حدث خطأ أثناء الإرسال، يرجى المحاولة لاحقاً.");
          console.error("EmailJS Error:", error);

          setTimeout(() => {
            setStatus("");
          }, 2000);
        }
      );
  };

  return (
    <section className="contact-section" id="contact">
      <div className="contact-wrapper w-100">
        
        {/* العنوان والوصف المتمركزين في المنتصف بالأعلى */}
        <div className="text-center mb-5">
          <h2 className="fw-bold fs-2">{t('contact.infoTitle') || "تواصل مع فريق SmartWare"}</h2>
          <p className="text-muted mx-auto" style={{ maxWidth: "600px" }}>
            {t('contact.infoDesc') || "هل لديك استفسارات حول ربط النظام بالمستودعات الخاصة بك أو تحديد أدوار المستخدمين؟ أرسل لنا وسنرد عليك في أقرب وقت."}
          </p>
        </div>

        {/* الحاوية التي تجمع النموذج والصورة بجانب بعضهما */}
        <div className="contact-container">
          
          {/* جهة النموذج (Form) */}
          <div className="contact-form">
            <h3 className="mb-4 fw-bold fs-4">{t('contact.formTitle') || "طلب استشارة أو دعم"}</h3>
            <form onSubmit={handleSubmit}>
              <input
                type="text"
                name="name"
                placeholder={t('contact.namePlaceholder') || "الاسم الكامل"}
                value={formData.name}
                onChange={handleChange}
                required
              />
              <input
                type="email"
                name="email"
                placeholder={t('contact.emailPlaceholder') || "البريد الإلكتروني"}
                value={formData.email}
                onChange={handleChange}
                required
              />
              <input
                type="tel"
                name="phone"
                placeholder={t('contact.phonePlaceholder') || "رقم الهاتف"}
                value={formData.phone}
                onChange={handleChange}
                required
              />
              <textarea
                name="message"
                rows="4"
                placeholder={t('contact.messagePlaceholder') || "تفاصيل الطلب أو الاستفسار"}
                value={formData.message}
                onChange={handleChange}
                required
              ></textarea>
              <button type="submit">
                {t('contact.submitBtn') || "إرسال الطلب"}
              </button>
            </form>

            {status && (
              <div className="alert alert-info mt-3 text-center" role="alert">
                {status}
              </div>
            )}
          </div>

          {/* جهة الصورة */}
          <div className="contact-info">
            <div className="image-wrapper">
              <img src={contactSvg} alt="SmartWare Support" />
            </div>
          </div>

        </div>
      </div>
    </section>
  );
}