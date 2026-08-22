import React from 'react';
import { useTranslation } from 'react-i18next';
import { getArticlesData } from '../../data/smartWareData';

export const Articles = () => {
  const { t } = useTranslation();
  
  // استدعاء الدالة لجلب المقالات المترجمة
  const articlesData = getArticlesData(t);

  return (
    <section className="articles" id="articles">
      {/* عنوان القسم المترجم */}
      <h2 className="main-title">{t('nav.articles')}</h2>

      <div className="container">
        {articlesData.map((item) => (
          <div className="box" key={item.id}>
            <img src={item.img} alt={item.title} />
            
            <div className="content">
              <h3>{item.title}</h3>
              <p>{item.desc}</p>
            </div>

            <div className="info">
              <a href="#read">{t('articles.readMore') || 'Read More'}</a>
              {/* أيقونة السهم تتغير حسـب اتجاه اللغة */}
              <i className={`fas ${t('dir') === 'rtl' ? 'fa-long-arrow-alt-left' : 'fa-long-arrow-alt-right'}`}></i>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
};