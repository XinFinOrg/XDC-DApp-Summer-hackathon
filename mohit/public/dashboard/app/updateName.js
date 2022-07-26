/* eslint-disable no-undef */
/* eslint-disable no-unused-vars */
console.log('hello')
async function updateName (e) {
  e.preventDefault()
  const name = $('#name-update').val()
  console.log(name)
  const requestOptions = {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    withCredentials: true
  }

  await axios.post('/user/dashboard/updatename', { name }, requestOptions)
    .then((res) => {
      console.log(res)
      showSuccessToast(res.data.message)
    })
    .catch((err) => {
      if (err.response) {
        console.log(err.response)
        showErrorToast(err.response.data.message)
      }
    })
}

$('#update-name-button').click(updateName)
