/* eslint-disable no-undef */
/* eslint-disable no-unused-vars */

async function addEvent (e) {
  e.preventDefault()
  const form = $('#add-event-form')[0]
  console.log(form.enctype)
  const formData = new FormData(form)

  requestOptions = {
    method: 'POST',
    headers: { 'Content-Type': form.enctype },
    withCredentials: true
  }
  //   const response = await fetch('/user/event', requestOptions)
  //   const responseData = response.json()
  await axios.post('/user/event', formData, requestOptions)
    .then((res) => {
      console.log(res)
      showSuccessToast(res.data.message)
      form.reset()
    })
    .catch((err) => {
      if (err.response) {
        console.log(err.response)
        showErrorToast(err.response.data.message)
      }
    })
}

$('#add-event-button').click(addEvent)
