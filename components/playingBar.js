import React, { useContext} from 'react';
import { AudioHandler } from '../pages/dashboard'

function PlayingBar(){

    const context = useContext(AudioHandler)

    return (
        context?.audioData?.duration === 0 ?
        <div></div>
        :
        <div className=" fixed border-2 px-5 border-white bottom-0 shadow-md py-5 rounded-t-md rounded-r-md bg-pink-500  text-white min-w-1/5 max-w-xs break-words flex items-start flex-col justify-around bg-primay">
            <div className="w-full">
                Now playing: <br/>
                <div className="text-xl font-semibold mb-5 mt-1">
                    {context.audioData.title}
                </div>
            </div>
            
            <button className="flex-1">
                {context.audioData.currentTime}s / {context.audioData.duration }s
            </button>
            <progress id="file" value={context?.audioData?.currentTime} max={context?.audioData?.duration} className="rounded-lg overflow-hidden mt-2">  </progress>
            
            
            
            

        </div>
    )
}

export default PlayingBar