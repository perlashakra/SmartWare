import { Route, Routes } from 'react-router-dom';
import './App.css';
import Login from './Pages/Auth/Login';
import Dashboard from './Pages/Dashboard/Dashboard';
import RequireAuth from './Pages/Auth/RequireAuth';
import Categories from './Pages/Dashboard/Categories';
import Products from './Pages/Dashboard/Products';
import Facilities from './Pages/Dashboard/Facilities';
import { Header } from './Components/Website/Header';
import { Landing } from './Components/Website/Landing';
import { Features } from './Components/Website/Features';
import { Articles } from './Components/Website/Articles';
import { Team } from './Components/Website/Team';
import { Testimonials } from './Components/Website/Testimonials';
import { FAQ } from './Components/Website/FAQ';
import Contact from './Components/Website/Contact';
import Err404 from './Pages/Auth/404';
import Err403 from './Pages/Auth/403';
import Footer from './Components/Website/Footer';
import Admin from './Pages/Dashboard/Admin';
import FacilityReview from './Pages/Dashboard/FacilityReview';

const WebsiteHomePage = () => {
  return (
    <>
      <Header />
      <main>
        <Landing />
        <Features />
        <Articles />
        <Team />
        <Testimonials />
        <FAQ />
        <Contact />
        <Footer />
      </main>
    </>
  );
};

function App() {
  return (
    <div className="App">
      <Routes>
        {/* Public Routes */}
        <Route path="/" element={<WebsiteHomePage />} />
        <Route path="/login" element={<Login />} />
        <Route path="/403" element={<Err403 />} />

        {/* Protected Dashboard Routes */}
        <Route element={<RequireAuth allowedRole={["super_admin"]} />}>
          <Route path="/dashboard" element={<Dashboard />}>
            <Route path="categories" element={<Categories />} />
            <Route path="products" element={<Products />} />
            <Route path="facilities" element={<Facilities />} />
            <Route path="facilities/review/:id" element={<FacilityReview />} />
            <Route path="admin-requests" element={<Admin />} />
          </Route>
        </Route>

        {/* Catch-All 404 Route */}
        <Route path="*" element={<Err404 />} />
      </Routes>
    </div>
  );
}

export default App;