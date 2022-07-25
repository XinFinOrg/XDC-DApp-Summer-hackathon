import React, { useEffect,useContext, useState } from 'react'
import { useRef } from 'react';
import { AudioHandler } from '../pages/explore';

function PodcastCard(props){
    const reference = useRef();
    const context = useContext(AudioHandler)
    const [playPause, setPlayPaus] = useState("/playBtn.svg")
    const [intervalID, setIntervalID] = useState()

    useEffect(() => {
        if(reference.current.duration === reference.current.currentTime){
            context.setAudioDate({
            duration:0,
            currentTime:0
            })
            setPlayPaus("/playBtn.svg")
            clearInterval(intervalID)
            console.log("song reached its end")
        }
    }, [reference.current?.currentTime])

    useEffect(() => {
        if(context.playingState !== props.k){
            reference.current.currentTime = 0
            setPlayPaus("/playBtn.svg")
            clearInterval(intervalID)   
        }
    }, [context.playingState])

    function playAudio(){
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
            author:props.data.author,
            playPause:playAudio,
            id:props.data.id
        })
    }

    return (
        <div className="p-5 border-2 border-black transition-all duration-200 mt-5 rounded-2xl  hover:shadow-xl  ">
            <audio className="audio-element" ref={reference}>
                <source src={props.data.link}></source>
            </audio>
            <div className="px-8 mt-5 text-xl break-words">
            {props.data.name}
            <br/>
            <div className="text-right my-2 text-sm"> by: {props.data.author.slice(0, 10).concat('...')}</div>
            </div>

            <div className=" flex items-center justify-between px-5 mt-12">
                <div className=" text-xl">
                    {props.data.category}
                </div>
                <div className="px-5" onClick={playAudio}>
                    <img src={playPause} className="img-fluid" width="40" />
                </div>
            </div>
        </div>

    )
}

export default PodcastCard