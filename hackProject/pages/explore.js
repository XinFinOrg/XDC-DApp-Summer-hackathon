import React, { createContext, useContext, useState, useEffect } from 'react'
import PlayingBarExplore from '../components/playingBarExplore'
import { Config } from './_app'
import toast, { Toaster } from 'react-hot-toast';
import Head from 'next/head'
import PodcastCard from '../components/podcastCard';

let AudioHandler = createContext()

function Explore() {
    const [audioData, setAudioData] = useState({currentTime:0,duration:0})
    const [userPodcasts, setUserPodcasts] = useState([])
    const [modalState, setModalState] = useState(true)
    const [playingState, setPlayingState] = useState(0)
    const [tab, setTab] = useState("all")
    const context = useContext(Config)

    let rem;

    useEffect(() => {
        if(context.sawtiContract != undefined){
            if(userPodcasts === undefined || userPodcasts.length === 0){
               
                if(tab === "all"){
                    context.sawtiContract.getAllPodcasts().then((data)=>{
                        setUserPodcasts(data);
                    });
                }
                else if(tab === "tech"){
                    context.sawtiContract.getTechPodcasts().then((data)=>{
                        setUserPodcasts(data);
                    });
                } 
                else if(tab === "fiction"){
                    context.sawtiContract.getFictionPodcasts().then((data)=>{
                        setUserPodcasts(data);
                    }); 
                }
                else {
                    context.sawtiContract.getMiscPodcasts().then((data)=>{
                        setUserPodcasts(data);
                    }); 
                }             
            }
        }

    }, [context, tab])

    useEffect(async() => {
        const {ethereum } = window;
        const accounts = await ethereum.request({ method: 'eth_accounts' });

        if(accounts.length === 0){
            connectWalletAlert();
        }

    }, [])

    function connectWalletAlert(){
        toast.error('Please connect your wallet',{  
            duration: 4000,
            position: 'bottom-center',
        });
    }

    return (
        <AudioHandler.Provider value={{
            audioData:audioData,
            setAudioData:setAudioData,
            setModalState:setModalState,
            modalState:modalState,
            setPlayingState:setPlayingState,
            playingState:playingState
        }} >
        <Head>
            <title>Sawti | Explore</title>
            <link rel="icon" href="/favicon.ico" />
        </Head>    

        <div className="min-h-screen w-full bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-violet-500">
            <div className="flex items-center justify-center flex-col bg-gradient-to-r from-indigo-200 via-red-200 to-yellow-100 text-white px-5 pb-10">
                
                <div className=" text-5xl pt-24 font-bold bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-violet-500">
                    Explore
                </div>
                <div className="pt-4 text-xl pb-10 text-center bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-violet-500">
                    Browse through various Audios across many categories
                </div>
                <div className="flex items-center justify-center flex-wrap pb-10 text-black">
                    <div onClick={()=>{setUserPodcasts([]);setTab("all");}} className={` cursor-pointer border-2 border-pink-800 mb-5 p-1 px-5 rounded-full mx-3 ${tab === "all" ? "bg-pink-500 font-semibold text-white":""}`}>
                        All
                    </div>
                    <div onClick={()=>{setUserPodcasts([]);setTab("tech")}} className={` cursor-pointer border-2 border-pink-800 mb-5 p-1 px-5 rounded-full mx-3 ${tab === "tech" ? "bg-pink-500 font-semibold text-white":""}`}>
                        Tech
                    </div>
                    <div onClick={()=>{setUserPodcasts([]);setTab("fiction")}} className={` cursor-pointer border-2 border-pink-800 mb-5 p-1 px-5 rounded-full mx-3 ${tab === "fiction" ? "bg-pink-500 font-semibold text-white":""}`}>
                        Fiction
                    </div>
                    <div onClick={()=>{setUserPodcasts([]);setTab("misc")}} className={` cursor-pointer border-2 border-pink-800 mb-5 p-1 px-5 rounded-full mx-3 ${tab === "misc" ? "bg-pink-500 font-semibold text-white":""}`}>
                        Misc
                    </div>
                </div>
            </div>
            <div >
            {
                    userPodcasts.length === 0 ?
                    <h1 className="text-center w-full mx-auto mt-5">No Audios</h1>
                    :
                    <div className="md:mx-auto px-2 md:px-10 max-w-full grid grid-cols-1 md:grid-cols-3 gap-5 mx-10 pb-5 ">
                        {userPodcasts.map((data,key)=>{
                                return(
                                    <div key={key}>
                                        <PodcastCard data={data} k={key}/>
                                    </div>
                                )
                            })
                        }
                    </div>
                    
                }
            </div>
            <PlayingBarExplore/>
            <Toaster/>
        </div>

        </AudioHandler.Provider>

    )



}


export default Explore
export { AudioHandler };