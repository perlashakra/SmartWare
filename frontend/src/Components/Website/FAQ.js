import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { getFaqData } from '../../data/smartWareData';

export const FAQ = () => {
  const { t } = useTranslation();
  
  // جلب الأسئلة المترجمة
  const faqData = getFaqData(t);

  // الاحتفاظ بالرقم المفتوح، null يعني جميع الأسئلة مغلقة
  const [openIndex, setOpenIndex] = useState(null);

  const toggleAccordion = (index) => {
    // إذا كان مفتوحاً واضغطنا عليه يغلق (null)، وإلا يفتح السؤال المحدد
    setOpenIndex(openIndex === index ? null : index);
  };

  return (
    <section className="faq py-5 bg-white" id="faq">
      {/* عنوان القسم المترجم */}
      <h2 className="main-title">
        {t('nav.faq')}
      </h2>
      <div className="container col-lg-8 mx-auto">
        <div className="accordion" id="faqAccordion">
          {faqData.map((item, index) => {
            const isOpen = openIndex === index;

            return (
              <div className="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm" key={index}>
                <h2 className="accordion-header">
                  <button
                    className={`accordion-button fs-5 fw-bold ${!isOpen ? 'collapsed' : ''}`}
                    type="button"
                    onClick={() => toggleAccordion(index)}
                  >
                    {item.q}
                  </button>
                </h2>
                <div className={`accordion-collapse collapse ${isOpen ? 'show' : ''}`}>
                  <div className="accordion-body text-secondary fs-6 lh-base">
                    {item.a}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
};