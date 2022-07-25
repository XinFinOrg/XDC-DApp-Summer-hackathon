const { readFileSync, writeFileSync, readdirSync, rmSync, existsSync, mkdirSync } = require('fs')
const { create } = require('ipfs-http-client')
require('dotenv').config()

const projectId = process.env.INFURA_API_ID
const projectSecret = process.env.INFURA_API_SECRET
const auth = 'Basic ' + Buffer.from(projectId + ':' + projectSecret).toString('base64')
const client = create({
  host: 'ipfs.infura.io',
  port: 5001,
  protocol: 'https',
  headers: {
    authorization: auth,
  },
})

// const client = create({ url: 'https://ipfs.infura.io:5001/api/v0' })

const template = `
    <svg width="230" height="230" viewBox="0 0 230 230" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- weapon -->
        <!-- wing -->
        <!-- engine -->
        <!-- cabin -->
    </svg>
`

function randInt(max) {
  return Math.floor(Math.random() * (max + 1))
}

function randElement(arr) {
  return arr[Math.floor(Math.random() * arr.length)]
}

function getLayer(name, skip = 0.0) {
  const svg = readFileSync(`./layers/${name}.svg`, 'utf-8')
  const re = /(?<=\<svg\s*[^>]*>)([\s\S]*?)(?=\<\/svg\>)/g
  const layer = svg.match(re)[0]
  return Math.random() > skip ? layer : ''
}

async function svgToPng(name) {
  const src = `./out/${name}.svg`
  const dest = `./out/${name}.png`

  await svg_to_png.convert(src, dest)

  //   const img = await sharp(src);
  //   await img.png({ progressive: true, adaptiveFiltering: true, palette: true }).toFile(dest);
}

async function createImage(weapon, wing, engine, cabin) {
  // Step 1: Generate images
  const image = template
    .replace('<!-- weapon -->', getLayer(`weapon${weapon}`))
    .replace('<!-- wing -->', getLayer(`wing${wing}`))
    .replace('<!-- engine -->', getLayer(`engine${engine}`))
    .replace('<!-- cabin -->', getLayer(`cabin${cabin}`))

  const shipCode = `${cabin}${engine}${wing}${weapon}`
  writeFileSync(`./out/${shipCode}.svg`, image)

  // Step 2: Upload images to IPFS
  const upload = await client.add(image)
  console.log(`Ship ${shipCode} uploaded to ipfs://${upload.path}`)

  // Step 3: Generate Metadata
  const meta = {
    name: `War Alpha Ship ${shipCode}`,
    description: 'A War Alpha Upgradable Spaceship',
    external_url: `https://waralphametaverse.com/assets/ships/${shipCode}.svg`,
    image: `ipfs://${upload.path}`,
    attributes: [
      {
        cabin,
        rarity: 0.25,
      },
      {
        engine,
        rarity: 0.25,
      },
      {
        wing,
        rarity: 0.25,
      },
      {
        weapon,
        rarity: 0.25,
      },
    ],
  }

  writeFileSync(`./out/${cabin}${engine}${wing}${weapon}.json`, JSON.stringify(meta))
}

// Create dir if not exists
if (!existsSync('./out')) {
  mkdirSync('./out')
}

// Cleanup dir before each run
readdirSync('./out').forEach((f) => rmSync(`./out/${f}`))

for (let weapon = 0; weapon <= 3; weapon++) {
  for (let wing = 0; wing <= 3; wing++) {
    for (let engine = 0; engine <= 3; engine++) {
      for (let cabin = 0; cabin <= 3; cabin++) {
        createImage(weapon, wing, engine, cabin)
      }
    }
  }
}
