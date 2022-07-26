import express from 'express'
import auth from './auth.js'
import dashboard from './dashboard.js'
import event from './event.js'
const router = express.Router()

router.use('/auth', auth)
router.use('/event', event)
router.use('/dashboard', dashboard)

export default router
