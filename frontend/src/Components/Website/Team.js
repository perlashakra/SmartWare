
import React from 'react';
import { useTranslation } from 'react-i18next';
import { getTeamData } from '../../data/smartWareData';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faFacebookF, faTwitter, faLinkedinIn, faYoutube } from '@fortawesome/free-brands-svg-icons';

export const Team = () => {
  const { t } = useTranslation();

  // جلب بيانات أعضاء الفريق المترجمة (الأسماء والأدوار الوظيفية)
  const teamData = getTeamData(t);

  return (
    <section className="team" id="team">
      <h2 className="main-title">{t('nav.team')}</h2>

      <div className="container">
        {teamData.map((member) => (
          <div className="box" key={member.id}>
            <div className="data">
              <img src={member.img} alt={member.name} />
              
              <div className="social">
                <a href={member.social.facebook} aria-label="Facebook">
                  <FontAwesomeIcon icon={faFacebookF} />
                </a>
                <a href={member.social.twitter} aria-label="Twitter">
                  <FontAwesomeIcon icon={faTwitter} />
                </a>
                <a href={member.social.linkedin} aria-label="LinkedIn">
                  <FontAwesomeIcon icon={faLinkedinIn} />
                </a>
                <a href={member.social.youtube} aria-label="YouTube">
                  <FontAwesomeIcon icon={faYoutube} />
                </a>
              </div>
            </div>

            <div className="info">
              <h3>{member.name}</h3>
              <p>{member.role}</p>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
};