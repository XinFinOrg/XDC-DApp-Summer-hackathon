import express from 'express'
import { sendLoginOtp, login, signingData, signatureVerifyAndLogin, logout } from '../../controllers/authentication'
import { verifyLoggedIn } from '../../middleware/verifyLoggedIn'

const router = express.Router()

router.post('/otp', sendLoginOtp)
router.post('/login', login)
router.get('/logout', verifyLoggedIn, logout)

router.post('/signingdata', signingData)
router.post('/cryptologin', signatureVerifyAndLogin)

export default router
