import React from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faFacebookF, faTwitter, faYoutube } from '@fortawesome/free-brands-svg-icons';
import { faMapMarkerAlt, faClock, faPhoneVolume } from '@fortawesome/free-solid-svg-icons';
import { getTeamData } from '../../data/smartWareData';

export default function Footer() {
  const { t } = useTranslation();
  const teamData = getTeamData(t);

  return (
    <footer className="main-footer" id="footer">
      <div className="footer-container">
        
        {/* العمود 1: اسم الموقع والسوشيال والتفاصيل */}
        <div className="footer-box">
          <h3 className="footer-logo">SmartWare</h3>
          <div className="social-icons">
            <a href="#" className="facebook" aria-label="Facebook">
              <FontAwesomeIcon icon={faFacebookF} />
            </a>
            <a href="#" className="twitter" aria-label="Twitter">
              <FontAwesomeIcon icon={faTwitter} />
            </a>
            <a href="#" className="youtube" aria-label="YouTube">
              <FontAwesomeIcon icon={faYoutube} />
            </a>
          </div>
          <p className="footer-desc">
            {t('footer.desc')}
          </p>
        </div>

        {/* العمود 2: الروابط المهمة */}
        <div className="footer-box">
          <ul className="footer-links">
            <li><a href="#features">{t('nav.features')}</a></li>
            <li><a href="#articles">{t('nav.articles')}</a></li>
            <li><a href="#team">{t('nav.team')}</a></li>
            <li><a href="#testimonials">{t('nav.testimonials')}</a></li>
            <li><a href="#faq">{t('nav.faq')}</a></li>
          </ul>
        </div>

        {/* العمود 3: معلومات التواصل مع الأيقونات */}
        <div className="footer-box">
          <div className="info-line">
            <FontAwesomeIcon icon={faMapMarkerAlt} className="info-icon" />
            <span>{t('footer.address')}</span>
          </div>
          <div className="info-line">
            <FontAwesomeIcon icon={faClock} className="info-icon" />
            <span>{t('footer.workingHours')}</span>
          </div>
          <div className="info-line">
            <FontAwesomeIcon icon={faPhoneVolume} className="info-icon" />
            <div className="phones">
              <span>+20123456789</span>
              <span>+20198765432</span>
            </div>
          </div>
        </div>

        {/* العمود 4: معرض صور الفريق (3x2) */}
        <div className="footer-box team-gallery">
          {teamData.slice(0, 6).map((member) => (
            <img key={member.id} src={member.img} alt={member.name} />
          ))}
        </div>

      </div>

      <div className="footer-copyright">
        {t('footer.copyright')}
      </div>
    </footer>
  );
}