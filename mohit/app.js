import createError from 'http-errors'
import express from 'express'
import path, { dirname } from 'path'
import cookieParser from 'cookie-parser'
import { fileURLToPath } from 'url'
import compression from 'compression'
import cors from 'cors'
import helmet from 'helmet'
import morgan from 'morgan'
import session from 'express-session'
import MongoStore from 'connect-mongo'

import config from './config'

import logger from './utilities/logger.js'
import routes from './routes'
import rateLimiter from './middleware/rateLimiter.js'
import { consumeQueue } from './utilities/queueUtils'
import { consumeMintTicketByCryptoQueue } from './controllers/user'
import './database'

const app = express()

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// view engine setup
app.set('views', path.join(__dirname, 'views'))
app.set('view engine', 'ejs')

app.use(express.json())
app.use(express.urlencoded({ extended: false }))
app.use(cookieParser())
app.use(rateLimiter)
const MemoryStore = session.MemoryStore

app.use(session({
  name: 'eventonchain.sid',
  secret: config.SESSION_SECRET,
  resave: false,
  saveUninitialized: true,
  store: MongoStore.create({
    mongoUrl: config.DATABASE.MONGO.URI,
    ttl: 14 * 24 * 60 * 60,
    autoRemove: 'native'
  }),
  cookie: { secure: false }
}))
app.use(compression())
app.use(cookieParser())
/* app.use(cors())
app.use(helmet({
  contentSecurityPolicy: {
    useDefaults: true,
    directives: {
      'img-src': ["'self'", '', 'https: data:']
    }
  }
})) */

try {
  consumeQueue(config.QUEUE.LIST.mint, consumeMintTicketByCryptoQueue)
} catch (err) {
  console.log(err)
}

app.use(express.static(path.join(__dirname, '/public')))
app.use('/', routes)

app.use(morgan('combined', {
  stream: logger.stream,
  skip: (req, res) => { // Skip to log health endpoint
    return req.url === '/health'
  }
}))

// catch 404 and forward to error handler
app.use(function (req, res, next) {
  next(createError(404))
})

// error handler
app.use(function (err, req, res, next) {
  // set locals, only providing error in development
  res.locals.message = err.message
  res.locals.error = req.app.get('env') === 'development' ? err : {}

  // render the error page
  res.status(err.status || 500)
  res.render('error')
})

export default app
