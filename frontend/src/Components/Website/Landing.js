import React from 'react';
import { useTranslation } from 'react-i18next';

export const Landing = () => {
  const { t } = useTranslation();

  return (
    <section className="landing position-relative overflow-hidden" id="landing">
      <div className="container min-vh-100 d-flex align-items-center justify-content-between position-relative pb-5">
        
        {/* النصوص والتعريف بنظام SmartWare */}
        <div className="text text-center text-md-start my-auto pe-md-4" style={{ zIndex: 2 }}>
          <h1 className="fw-bold display-5 mb-3 text-dark">
            {t('landing.welcome')}{' '}
            <span className="text-primary">{t('landing.brandName')}</span>
          </h1>
          <p className="fs-5 text-muted lh-lg mb-0" style={{ maxWidth: '500px' }}>
            {t('landing.description')}
          </p>
        </div>

        {/* الصورة الرئيسية */}
        <div className="image d-none d-md-block" style={{ zIndex: 2 }}>
          <img 
            src={require('./images/landing.svg').default} 
            alt="SmartWare Warehouse Automation" 
            className="img-fluid landing-img"
          />
        </div>

        {/* السهم المتحرك للتمرير لأسفل */}
        <a href="#articles" className="go-down position-absolute start-50 translate-middle-x text-primary fs-4" style={{ zIndex: 2 }}>
          <i className="fas fa-angle-double-down"></i>
        </a>

      </div>
    </section>
  );
};