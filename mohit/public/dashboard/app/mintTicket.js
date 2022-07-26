/* eslint-disable no-undef */

/* eslint-disable no-unused-vars */
async function mintTicket (e) {
  e.preventDefault()
  const eventId = e.target.getAttribute('value')
  console.log(e.target)
  console.log(eventId)
  let requestOptions = {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' },
    withCredentials: true
  }
  const res = await axios.get('/user/event/newpayment/' + eventId, {}, requestOptions)
  console.log(res)
  const payDetails = res.data.data.payDetails
  // const pay = await web3.eth.sendTransaction({ from: accountConnected, to: '0x8F52Ef5933925aa2e536c7c882A643ba4C0797b8', value: web3.utils.toWei('0.000001', 'ether') })
  const value = web3.utils.toWei(payDetails.amount.toString(), 'ether')
  console.log(value)
  await eventonchainContract.methods.payForTicket(payDetails.payId, value).send({ from: payDetails.address, value })
  requestOptions = {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    withCredentials: true
  }
  //   const response = await fetch('/user/event', requestOptions)
  //   const responseData = response.json()
  await axios.post('/user/event/mint', JSON.stringify({ eventId, paymentId: payDetails._id }), requestOptions)
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

$('.mint-event-button').click(mintTicket)
