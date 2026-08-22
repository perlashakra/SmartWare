import React from 'react';
import { useTranslation } from 'react-i18next';
import { getFeaturesData } from '../../data/smartWareData';

export const Features = () => {
  const { t } = useTranslation();

  // جلب ميزات النظام المترجمة
  const featuresData = getFeaturesData(t);

  return (
    <section className="features py-5 bg-white" id="features">
      {/* عنوان القسم المترجم */}
      <h2 className="main-title">
        {t('nav.features')}
      </h2>

      <div className="container">
        <div className="features-grid">
          {featuresData.map((item) => (
            <div className={`box ${item.colorClass}`} key={item.id}>
              <div className="img-holder">
                <img src={item.img} alt={item.title} />
              </div>
              <h2>{item.title}</h2>
              <p>{item.desc}</p>
              <a href="#contact" className="more-btn">
                {t('articles.readMore') || 'More'}
              </a>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};