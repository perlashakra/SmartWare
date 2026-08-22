import React from 'react';
import { useTranslation } from 'react-i18next';
import { getTestimonialsData } from '../../data/smartWareData';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faStar as fasStar } from '@fortawesome/free-solid-svg-icons';
import { faStar as farStar } from '@fortawesome/free-regular-svg-icons';

export const Testimonials = () => {
  const { t } = useTranslation();

  // جلب البيانات المترجمة (المسميات الوظيفية وآراء العملاء)
  const testimonialsData = getTestimonialsData(t);

  return (
    <section className="testimonials" id="testimonials">
      <h2 className="main-title">{t('nav.testimonials')}</h2>

      <div className="container">
        {testimonialsData.map((item) => (
          <div className="box" key={item.id}>
            <img src={item.img} alt={item.name} />
            <h3>{item.name}</h3>
            <span className="title">{item.title}</span>

            {/* رسم النجوم الملونة والرمادية حسب قيمة item.stars */}
            <div className="rate">
              {[...Array(5)].map((_, index) => (
                <FontAwesomeIcon
                  key={index}
                  icon={index < item.stars ? fasStar : farStar}
                  className={index < item.stars ? 'filled' : ''}
                />
              ))}
            </div>

            <p>{item.text}</p>
          </div>
        ))}
      </div>
    </section>
  );
};