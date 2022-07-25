import logo from './logo.svg';
import './App.css';
import Upload from './UploadPage';
import GalleryPage from './GalleryPage';
import Layout from './Layout';
import { BrowserRouter, Routes, Route } from "react-router-dom";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<GalleryPage />} />
          <Route path="upload" element={<Upload />} />
          <Route path="*" element={<Layout />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

export default App;
