import avatar1 from '../Components/Website/images/avatar-01.png';
import avatar2 from '../Components/Website/images/avatar-02.png';
import avatar3 from '../Components/Website/images/avatar-03.png';
import avatar4 from '../Components/Website/images/avatar-04.png';
import avatar5 from '../Components/Website/images/avatar-05.png';
import avatar6 from '../Components/Website/images/avatar-06.png';
import featureImg1 from '../Components/Website/images/Quality.jpg';
import featureImg2 from '../Components/Website/images/Time1.jpg';
import featureImg3 from '../Components/Website/images/location.jpg';
import articleImg1 from '../Components/Website/images/inventory1.svg';
import articleImg2 from '../Components/Website/images/role.svg';
import articleImg3 from '../Components/Website/images/Interaction.svg';
import articleImg4 from '../Components/Website/images/Design-stats-bro.svg';
import articleImg5 from '../Components/Website/images/Barcode-rafiki.svg';
import articleImg6 from '../Components/Website/images/Visual-data-rafiki.svg';
import teamImg1 from '../Components/Website/images/shahed.jpg';
import teamImg2 from '../Components/Website/images/nicole.jpg';
import teamImg4 from '../Components/Website/images/joelle.jpg';
import teamImg3 from '../Components/Website/images/yosif.jpg';
import teamImg5 from '../Components/Website/images/shahed2.jpg';
import teamImg6 from '../Components/Website/images/Antoan.jpg';





// 1. روابط القائمة
export const getNavLinks = (t) => [
  { id: 'features', label: t('nav.features') },
  { id: 'articles', label: t('nav.articles') },
  { id: 'team', label: t('nav.team') },
  { id: 'testimonials', label: t('nav.testimonials') },
  { id: 'faq', label: t('nav.faq') },
  { id: 'contact', label: t('nav.contact') },
  { id: 'login', label: t('nav.dashboard'), isPage: true },
];

// 2. بيانات الميزات
export const getFeaturesData = (t) => [
  {
    id: 1,
    title: t('features.item1.title'),
    colorClass: "quality",
    img: featureImg1,
    desc: t('features.item1.desc')
  },
  {
    id: 2,
    title: t('features.item2.title'),
    colorClass: "time",
    img: featureImg2,
    desc: t('features.item2.desc')
  },
  {
    id: 3,
    title: t('features.item3.title'),
    colorClass: "passion",
    img: featureImg3,
    desc: t('features.item3.desc')
  }
];

// 3. بيانات المقالات
export const getArticlesData = (t) => [
  {
    id: 1,
    title: t('articles.item1.title'),
    desc: t('articles.item1.desc'),
    img: articleImg1,
  },
  {
    id: 2,
    title: t('articles.item2.title'),
    desc: t('articles.item2.desc'),
    img: articleImg2,
  },
  {
    id: 3,
    title: t('articles.item3.title'),
    desc: t('articles.item3.desc'),
    img: articleImg3,
  },
  {
    id: 4,
    title: t('articles.item4.title'),
    desc: t('articles.item4.desc'),
    img: articleImg6,
  },
  {
    id: 5,
    title: t('articles.item5.title'),
    img: articleImg4,
    desc: t('articles.item5.desc')
  },
  {
    id: 6,
    title: t('articles.item6.title'),
    img: articleImg5,
    desc: t('articles.item6.desc')
  }
];

// 4. بيانات فريق العمل
export const getTeamData = (t) => [
  { 
    id: 1, 
    name: t('team.item1.name'), 
    role: "Super Admin", 
    img: teamImg1,
    social: { facebook: "#", twitter: "#", linkedin: "#", youtube: "#" }
  },
  { 
    id: 2, 
    name: t('team.item2.name'), 
    role: "Warehouse Admin", 
    img: teamImg2,
    social: { facebook: "#", twitter: "#", linkedin: "#", youtube: "#" }
  },
  { 
    id: 3, 
    name: t('team.item3.name'), 
    role: "Inventory Supervisor", 
    img: teamImg3,
    social: { facebook: "https://www.facebook.com/share/1EA42wE38C/", twitter: "#", linkedin: "www.linkedin.com/in/shahed-hael-ashour", youtube: "#" }
  },
  { 
    id: 4, 
    name: t('team.item4.name'), 
    role: "Warehouse Worker", 
    img: teamImg4,
    social: { facebook: "#", twitter: "#", linkedin: "#", youtube: "#" }
  },
  { 
    id: 5, 
    name: t('team.item5.name'), 
    role: "Logistics Specialist", 
    img: teamImg5,
    social: { facebook: "#", twitter: "#", linkedin: "#", youtube: "#" }
  },
  { 
    id: 6, 
    name: t('team.item6.name'), 
    role: "Quality Inspector", 
    img: teamImg6,
    social: { facebook: "#", twitter: "#", linkedin: "#", youtube: "#" }
  }
];

// 5. بيانات آراء العملاء
export const getTestimonialsData = (t) => [
  { id: 1, name: "Mohamed Farag", title: t('testimonials.item1.title'), img: avatar1, stars: 5, text: t('testimonials.item1.text') },
  { id: 2, name: "Mohamed Ibrahim", title: t('testimonials.item2.title'), img: avatar2, stars: 4, text: t('testimonials.item2.text') },
  { id: 3, name: "Shady Nabil", title: t('testimonials.item3.title'), img: avatar3, stars: 4, text: t('testimonials.item3.text') },
  { id: 4, name: "Amr Hendawy", title: t('testimonials.item4.title'), img: avatar4, stars: 5, text: t('testimonials.item4.text') },
  { id: 5, name: "Sherief Ashraf", title: t('testimonials.item5.title'), img: avatar5, stars: 3, text: t('testimonials.item5.text') },
  { id: 6, name: "Osama Mohamed", title: t('testimonials.item6.title'), img: avatar6, stars: 3, text: t('testimonials.item6.text') }
];

// 6. بيانات الأسئلة الشائعة
export const getFaqData = (t) => [
  { q: t('faq.item1.q'), a: t('faq.item1.a') },
  { q: t('faq.item2.q'), a: t('faq.item2.a') },
  { q: t('faq.item3.q'), a: t('faq.item3.a') }
];