import express from 'express'
import { healthCheck } from '../../utilities/serverUtils'
const router = express.Router()

/* GET users listing. */
router.get('/', async function (req, res) {
  const health = await healthCheck()
  try {
    res.send(health)
  } catch (error) {
    health.message = error
    res.status(503).send()
  }
})

export default router
