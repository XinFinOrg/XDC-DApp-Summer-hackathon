
const element = document.getElementById('loginWithEmail')
element.addEventListener('click', loginWithEmail)

function loginWithEmail () {
  url = '/auth/otp'
  const emailUser = document.getElementById('emailAddress').value
  const data = { email: emailUser }
  //  var raw = "{\n    \"email\":\"mbcse50@gmail.com\"\n}";

  const requestOptions = {
    method: 'GET',
    body: data,
    redirect: 'follow'
  }

  fetch('/auth/otp', requestOptions)
    .then(response => response.text())
    .then(result => console.log(result))
    .catch(error => console.log('error', error))
}
