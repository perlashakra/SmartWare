import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { getNavLinks } from '../../data/smartWareData';

export const Header = () => {
  const { t, i18n } = useTranslation();
  const navLinks = getNavLinks(t);

  const [isOpen, setIsOpen] = useState(false);

  const toggleMenu = () => {
    setIsOpen(!isOpen);
  };

  const toggleLanguage = () => {
    const newLang = i18n.language === 'ar' ? 'en' : 'ar';
    i18n.changeLanguage(newLang);
    document.dir = newLang === 'ar' ? 'rtl' : 'ltr';
  };

  return (
    <header className="header bg-white shadow-sm sticky-top" id="header">
      <nav className="navbar navbar-expand-lg navbar-light py-2">
        <div className="container">
          <a href="#landing" className="logo fw-bold text-primary text-decoration-none fs-3">
            SmartWare
          </a>

          <button
            className="navbar-toggler"
            type="button"
            onClick={toggleMenu}
            aria-expanded={isOpen}
            aria-label="Toggle navigation"
          >
            <span className="navbar-toggler-icon"></span>
          </button>

          <div className={`collapse navbar-collapse mt-3 mt-lg-0 ${isOpen ? 'show' : ''}`} id="navbarContent">
            <ul className="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center text-center">
              {navLinks.map((link) => (
                <li key={link.id} className="nav-item" onClick={() => setIsOpen(false)}>
                  {link.isPage ? (
                    <Link
                      to="/login"
                      className="nav-link text-dark px-3 fw-semibold"
                    >
                      {link.label}
                    </Link>
                  ) : (
                    <a
                      href={`#${link.id}`}
                      className="nav-link text-dark px-3 fw-semibold"
                    >
                      {link.label}
                    </a>
                  )}
                </li>
              ))}
            </ul>

            {/* هنا التعديل: استبدال حاوية الزر القديمة بهذه الحاوية */}
            <div className="d-flex justify-content-center justify-content-lg-start ms-lg-3 mt-3 mt-lg-0 pb-2 pb-lg-0">
              <button
                onClick={() => {
                  toggleLanguage();
                  setIsOpen(false);
                }}
                className="btn btn-outline-primary btn-sm px-4 py-1 fw-bold"
              >
                {i18n.language === 'ar' ? 'English' : 'عربي'}
              </button>
            </div>

          </div>
        </div>
      </nav>
    </header>
  );
};