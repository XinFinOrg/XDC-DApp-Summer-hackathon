export type AvatarProps = {
  url: string | undefined;
  domain: string;
};

export default function Avatar({ url, domain }: AvatarProps) {
  if (url) {
    return <img src={url} alt="Avatar record of the domain" className="avatar" />;
  } else {
    return (
      <svg xmlns="http://www.w3.org/2000/svg" width="270" height="270" fill="none"> <path fill="url(#B)" d="M0 0h270v270H0z"/><defs><filter id="A" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse" height="270" width="270"><feDropShadow dx="0" dy="1" stdDeviation="2" flood-opacity=".225" width="200%" height="200%"/></filter></defs>
<path className="st0" d="M270.3,270.3H0V18.7C0,8.4,8.4,0,18.7,0h233c10.3,0,18.7,8.4,18.7,18.7v251.6H270.3z"/>
	<g>
		
  <path className="st1" fill="#fff" d="M63.4,112.2c-3-0.6-6.1-1-8.9-1.9C38,104.7,28,93.5,24.6,76.4c-0.2-1.1-0.3-2.2-0.5-3.4   c2.5-1.5,4.8-2.9,7.3-4.5c-2.4-1.5-4.7-2.9-7.2-4.4c0.8-8.2,3.9-15.6,9-22c7.3-9.1,16.8-14.5,28.3-16c26.9-3.3,46.5,17.2,48.3,37.8   c-2.4,1.4-4.8,2.9-7.5,4.5c2.6,1.6,5,3.1,7.5,4.6c-0.1,3.3-0.8,6.5-1.9,9.6C102.8,97.6,92.4,107,77.2,111c-2.2,0.6-4.4,0.8-6.6,1.2   C68.2,112.2,65.8,112.2,63.4,112.2z M27.5,62.9c3.2,1.9,6.4,3.8,9.8,5.8c-3.4,2-6.6,3.9-9.9,5.9c0.9,7.5,3.9,14.1,8.6,19.9   c7.6,9.2,17.5,14.3,29.5,14.8c21.8,1,39.3-15.6,41.5-34.5c-3.3-2-6.7-4-10.2-6.1c3.5-2.1,6.8-4,10.1-5.9c-2-16.8-18-34.8-41-34   C42.9,29.5,29,48,27.5,62.9z"/>
  <path className="st1" fill="#fff" d="M53.2,52.5c2.2,0,4.1,0,6.1,0c2.5,4,5,7.9,7.6,12.1c2.5-4.2,4.9-8.1,7.3-12c1.8,0,3.6,0,5.6,0   c-3.5,5.5-6.9,10.8-10.3,16.2c3.4,5.4,6.8,10.9,10.4,16.5c-2.1,0-4,0-6.1,0c-2.4-3.9-4.9-7.9-7.6-12.1c-2.5,4.2-4.9,8.1-7.3,12.1   c-1.8,0-3.5,0-5.6,0c3.5-5.5,6.9-10.8,10.4-16.2C60.2,63.5,56.8,58.1,53.2,52.5z"/>
  </g>

		
	<defs><linearGradient id="B" x1="0" y1="0" x2="270" y2="270" gradientUnits="userSpaceOnUse"><stop stop-color="#5FB2D3"/><stop offset="1" stop-color="#E1BA6A" stop-opacity=".99"/></linearGradient></defs><text x="32.5" y="231" font-size="27" fill="#fff" filter="url(#A)" font-family="Plus Jakarta Sans,DejaVu Sans,Noto Color Emoji,Apple Color Emoji,sans-serif" font-weight="bold">{domain}</text></svg>

    );
  }
}
