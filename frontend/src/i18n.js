import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import LanguageDetector from "i18next-browser-languagedetector";

import arTranslation from "./locales/ar.json";
import enTranslation from "./locales/en.json";

i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources: {
      ar: { translation: arTranslation },
      en: { translation: enTranslation },
    },
    fallbackLng: "ar",
    interpolation: {
      escapeValue: false,
    },
  });

// 🌟 التعديل هنا: التحكم بالاتجاه حسب رابط الصفحة عند تغيير اللغة
i18n.on("languageChanged", (lng) => {
  const isDashboard = window.location.pathname.includes("/dashboard");

  if (isDashboard) {
    document.documentElement.dir = "ltr";
  } else {
    document.documentElement.dir = lng === "ar" ? "rtl" : "ltr";
  }
});

export default i18n;