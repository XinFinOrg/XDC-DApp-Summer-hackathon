import Head from "next/head";
import Link from "next/dist/client/link";
import Image from "next/image";

export default function Home() {
  return (
    <div className="flex flex-col items-center justify-center min-h-screen">
      <Head>
        <title>Sawti</title>
        <link rel="icon" href="/favicon.ico" />
      </Head>

      <div className=" px-20 min-h-screen w-full bg-gradient-to-r from-indigo-200 via-red-200 to-yellow-100">
        <div className="grid grid-rows-3 grid-flow-col gap-4 py-12">
          <div className="row-span-3 ...">
            {" "}
            <img src="/mic.svg" className="img-fluid" width="300" />
          </div>
          <div className="col-span-2 ...">
            <div className=" static  leading-20 text-4xl sm:text-6xl py-10 drop-shadow-lg  text-center font-extrabold">
              An XDC Netwrok <a className="underline decoration-8 decoration-pink-500"> Audio experience </a> 🎤
            </div>
          </div>
          <div className="row-span-1 col-span-2 ...">
            <div className=" text-xl text-center ">
              Sawti allows you to publish all your audio in a decentralized way, its powered By <a className="underline decoration-2 decoration-pink-500">IPFS</a> 
              <div className="text-center">
                <Link href="/dashboard">
                  <a>
                    <button className=" hover:bg-transparent bg-pink-500 hover:text-pink-500 transition-all duration-300 border border-pink-800 text-black py-3 px-10 mt-10 mb-10">START TODAY</button>
                  </a>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
