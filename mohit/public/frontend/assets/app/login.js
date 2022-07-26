/* eslint-disable no-unused-vars */

// login form
function userLogin (e) {
  e.preventDefault()
  const emailAddress = document.getElementById('user_email').value
  const data = JSON.stringify({
    email: emailAddress
  })

  const requestOptions = {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: data,
    credentials: 'same-origin',
    redirect: 'follow'
  }

  fetch('/user/auth/otp', requestOptions)
    .then(response => response.text())
    .then(result => {
      // hide sign up
      document.getElementById('current_user_email').value = emailAddress
      document.getElementById('user-login-signup').style.display = 'none'
      document.getElementById('OtpForm').style.display = 'block'
    })
    .catch(error => console.log('error', error))
  return false
}

// Verify OTP
function loginOtp () {
  const emailAddress = document.getElementById('current_user_email').value
  const otp = document.getElementById('otp').value

  const data = JSON.stringify({
    email: emailAddress,
    otp
  })

  const requestOptions = {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: data,
    credentials: 'same-origin',
    redirect: 'follow'
  }

  fetch('/user/auth/login', requestOptions)
    .then(response => response.text())
    .then(result => {
      // hide sign up
      const response = JSON.parse(result)
      console.log(typeof (response))
      if (response.status === 'success') {
        console.log(response.status)
        window.location.href = '/user/dashboard'
      } else {
        alert('OTP not valid')
      }
    })
    .catch(error => console.log('error', error))
  return false
}

document.getElementById('email-login-button').addEventListener('click', userLogin)
