import express from 'express'
import userRoute from './user'
import adminRoute from './admin'
import healthRoute from './health'
// import verifyAPIKey from '../middleware/verifyAPIKey.js'
// import { homepage } from '../controllers/home'

const router = express.Router()

// landing page
router.get('/', (req, res) => {
  res.render('frontend/index')
})

router.use('/user', userRoute)
router.use('/admin', adminRoute)
router.use('/health', healthRoute)

export default router
