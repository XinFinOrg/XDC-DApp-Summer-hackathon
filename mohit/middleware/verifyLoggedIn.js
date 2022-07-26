
export function verifyLoggedIn (req, res, next) {
  console.log(req.session)
  if (!req.session.loggedIn) {
    // return res.status(403).send({
    //   status: 'error',
    //   message: 'Please Log In First',
    //   error_code: 403,
    //   data: null // or optional error payload
    // })

    return res.redirect('/')
  }

  next()
}

export function verifyLoggedInForApi (req, res, next) {
  console.log(req.session)
  if (!req.session.loggedIn) {
    return res.status(403).send({
      status: 'error',
      message: 'Please Log In First',
      error_code: 403,
      data: null // or optional error payload
    })
  }

  next()
}
