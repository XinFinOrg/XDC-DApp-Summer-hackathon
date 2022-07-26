import express from 'express'
import { getEvent, createEvent, getNewPaymentSession, ticketMintByCrypto, checkInTicket } from '../../controllers/user'
import { verifyLoggedInForApi } from '../../middleware/verifyLoggedIn.js'
import { uploadEventPass } from '../../services/fileUpload.js'

const router = express.Router()

router.get('/', getEvent)
router.post('/', verifyLoggedInForApi, uploadEventPass.single('eventPassImage'), createEvent)
router.get('/newpayment/:eventId', verifyLoggedInForApi, getNewPaymentSession)
router.post('/mint', verifyLoggedInForApi, verifyLoggedInForApi, ticketMintByCrypto)
router.get('/ticket/checkin/:ticketId', checkInTicket)

export default router
