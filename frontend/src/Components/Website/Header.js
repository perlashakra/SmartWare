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
      <nav className="navbar navbar-expand-lg navbar-light p-0">
        <div className="container">
          <a href="#landing" className="logo fw-bold text-primary text-decoration-none fs-3 py-3">
            SmartWare
          </a>

          <button
            className="navbar-toggler my-2"
            type="button"
            onClick={toggleMenu}
            aria-expanded={isOpen}
            aria-label="Toggle navigation"
          >
            <span className="navbar-toggler-icon"></span>
          </button>

          <div className={`collapse navbar-collapse ${isOpen ? 'show' : ''}`} id="navbarContent">
            <ul className="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-stretch text-center">
              {navLinks.map((link) => (
                <li key={link.id} className="nav-item d-flex align-items-center" onClick={() => setIsOpen(false)}>
                  {link.isPage ? (
                    <Link
                      to="/login"
                      className="nav-link-custom d-flex justify-content-center align-items-center text-dark text-decoration-none px-3 fw-semibold"
                    >
                      {link.label}
                    </Link>
                  ) : (
                    <a
                      href={`#${link.id}`}
                      className="nav-link-custom d-flex justify-content-center align-items-center text-dark text-decoration-none px-3 fw-semibold"
                    >
                      {link.label}
                    </a>
                  )}
                </li>
              ))}
            </ul>

            <div className="d-flex justify-content-center justify-content-lg-start ms-lg-3 my-3 my-lg-0">
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