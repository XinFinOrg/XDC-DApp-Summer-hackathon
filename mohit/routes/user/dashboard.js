import express from 'express'
import { verifyLoggedIn } from '../../middleware/verifyLoggedIn.js'
import { dashboard, myEvents, updateName, myTickets, myTransactions, checkIn, addEvent, burnedTickets } from '../../controllers/user/dashboard.js'

const router = express.Router()

// user dashboard
router.get('/', verifyLoggedIn, dashboard)

router.get('/addevent', verifyLoggedIn, addEvent)

router.get('/profile', verifyLoggedIn, (req, res) => {
  res.render('dashboard/profile', { userAddress: req.session.address })
})

router.get('/myevents', verifyLoggedIn, myEvents)

router.get('/mytickets', verifyLoggedIn, myTickets)
router.get('/mytransactions', verifyLoggedIn, myTransactions)
router.get('/checkin/:eventId', verifyLoggedIn, checkIn)

router.get('/mintlist', verifyLoggedIn, (req, res) => {
  res.render('dashboard/mintlist')
})

router.get('/burnedtickets', verifyLoggedIn, burnedTickets)

router.post('/updatename', verifyLoggedIn, updateName)

export default router
