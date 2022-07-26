import config from '../config'

function verifyAPIKey (req, res, next) {
  const apiKey = req.headers['x-api-key']

  if (!apiKey || apiKey !== config.API_KEY) {
    return res.status(403).send({
      status: 'error',
      message: 'API key not valid',
      error_code: 403,
      data: null // or optional error payload
    })
  }

  next()
}

export default verifyAPIKey
