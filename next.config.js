/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  swcMinify: true,
}

module.exports = {
  nextConfig,
  env :{
    NEXT_MNENOMIC: process.env.MNENOMIC,
    NEXT_MUMBAI: process.env.MUMBAI,
    NEXT_PUBLIC_AUTH_CODE: process.env.NEXT_PUBLIC_AUTH_CODE,
    NEXT_DEEPAI_API_KEY: process.env.NEXT_DEEPAI_API_KEY,
    NEXT_NFTPort: process.env.NEXT_NFTPort,
  },
  images:{
    unoptimized: true
  },
  experimental: {
    images: {
      unoptimized: true,
    },
  },
}
