import React, { useEffect,useContext, useState } from 'react'
import { useRef } from 'react';
import { AudioHandler } from '../pages/dashboard';
import { create as ipfsHttpClient } from 'ipfs-http-client'

const client = ipfsHttpClient({ host: 'ipfs.infura.io', port: 5001, protocol: 'https' })

function Podcast(props){
    const reference = useRef();
    const context = useContext(AudioHandler);


    const [playPause, setPlayPause] = useState("/playBtn.svg")
    const [intervalID, setIntervalID] = useState()

    
    let d = new Date(props.data.timestamp*1000);

    useEffect(()=>{
        if(reference.current.duration === reference.current.currentTime){
            context.setAudioData({
                duration:0,
                currentTime:0
            })
            setPlayPause("/playBtn.svg")
            clearInterval(intervalID)
            console.log("ended")
        }
    },[reference.current?.currentTime])

    useEffect(()=>{
        if(context.playingState != props.k){
            reference.current.currentTime = 0 ; 
            setPlayPause("/playBtn.svg")
            clearInterval(intervalID)
        }
    },[context.playingState])

    
  

    function playAudio() {
        if(reference.current.paused){
            reference.current.play()
            context.setPlayingState(props.k)
            setPlayPause("/pauseBtn.svg")
            setIntervalID(setInterval(playData, 1000))
            
        }

        else{
            reference.current.pause()
            context.setPlayingState(0)
            setPlayPause("/playBtn.svg")
            clearInterval(intervalID)
        }
    }

    function playData(){
        context.setAudioData({
            duration:parseInt(reference.current?.duration),
            currentTime:parseInt(reference.current?.currentTime),
            title:props.data.name,
        })
    }

    
    return (
        <div className="flex p-5 flex-col lg:flex-row items-center justify-between border-2 border-pink-500 transition-all duration-200 mt-5 rounded-md w-2/5 hover:shadow-xl">
            <audio className="audio-element" ref={reference}>
                <source src={props.data.link}></source>
            </audio>
            <div className="px-5   " onClick={playAudio}>
                <img src={playPause} className="img-fluid " width="30" />
            </div>
            <div className="px-5 my-3 lg:my-0 bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-violet-500">
                {props.data.name}
            </div>
            
            <div className="px-5 bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-violet-500 ">
                {d.toLocaleDateString("en-US")}
            </div>
        </div>

    )
}

export default Podcast