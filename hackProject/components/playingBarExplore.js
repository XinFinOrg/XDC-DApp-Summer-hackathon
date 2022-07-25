import { ethers } from 'ethers';
import React, { useContext,useState } from 'react'
import { AudioHandler } from '../pages/explore'
import { Config } from '../pages/_app';
import Link from 'next/dist/client/link'

function PlayingBarExplore(){

    const context = useContext(AudioHandler)
    const context2 = useContext(Config)

    const [matic, setMatic] = useState(null)
    const [showInput, setShowInput] = useState(false)

    return(
        context?.audioData?.duration === 0 || context?.audioData?.duration === context?.audioData?.currentTime ?
        <div></div>
        :
        <div className=" fixed border-2 px-5 border-white bottom-0 shadow-md py-5 rounded-t-md rounded-r-md bg-primary  text-white min-w-1/5 max-w-xs break-words flex items-start flex-col justify-around bg-primay">
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
            <div className="flex justify-between w-full mt-5">
                <div>
                <button onClick={context?.audioData?.playPause} className="flex-1 bg-black h-12 w-12  rounded-full ml-0 mr-2">
                        ⏯
                    </button>
                </div>
            </div>
            <div>
                    <Link href={`/podcast/${context?.audioData?.id}`}>
                        <a>
                            <button className="flex-1 bg-black h-12 w-12  rounded-full ml-0 mr-2">
                                🔗
                            </button>
                        </a>
                    </Link>
            </div>
            <div>
                    <button onClick={()=>{setShowInput(!showInput)}} className="flex-1 bg-black h-12 w-12  rounded-full ml-0 mr-2">
                        💸
                    </button>
            </div>
        

        </div>
    )

}

export default PlayingBarExplore