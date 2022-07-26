/* eslint-disable no-undef */
/* eslint-disable no-unused-vars */
async function checkInTicket (e) {
  console.log('checkin')
  e.preventDefault()
  const ticketId = e.target.getAttribute('value')

  const requestOptions = {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' },
    withCredentials: true
  }

  await axios.get('/user/event/ticket/checkin/' + ticketId, { }, requestOptions)
    .then((res) => {
      if (res.data.data.result) {
        showSuccessToast(res.data.message)
        $('#checkin-ticket-button-' + ticketId).hide()
        $('#ticket-status-' + ticketId).text('CHECKEDIN')
        $('#issue-poap-button-' + ticketId).show()
      } else {
        showErrorToast(res.data.message)
      }
    })
    .catch((err) => {
      if (err.response) {
        console.log(err.response)
        showErrorToast(err.response.data.message)
      }
    })
}

$('a.checkin-ticket-button').click(checkInTicket)
