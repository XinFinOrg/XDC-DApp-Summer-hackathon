import React from 'react';

//Graveyard ecosystem logos
import hauntedLogo from '../../assets/img/haunted_cash.png';
import hShareLogo from '../../assets/img/haunted_share.png';
import hauntedLogoPNG from '../../assets/img/haunted_cash.png';
import hShareLogoPNG from '../../assets/img/haunted_share.png';
import hBondLogo from '../../assets/img/haunted_bond.png';

import hauntedFtmLpLogo from '../../assets/img/haunted_cash.png';
import hshareFtmLpLogo from '../../assets/img/haunted_share.png';

import wxdcLogo from '../../assets/img/xdc_logo.jpg';


const logosBySymbol: { [title: string]: string } = {
  //Real tokens
  //=====================
  HAUNTED: hauntedLogo,
  HAUNTEDPNG: hauntedLogoPNG,
  HSHAREPNG: hShareLogoPNG,
  HSHARE: hShareLogo,
  HBOND: hBondLogo,
  WXDC: wxdcLogo,
  'HAUNTED-XDC-LP': hauntedFtmLpLogo,
  'HSHARE-XDC-LP': hshareFtmLpLogo,
};

type LogoProps = {
  symbol: string;
  size?: number;
};

const TokenSymbol: React.FC<LogoProps> = ({ symbol, size = 64 }) => {
  if (!logosBySymbol[symbol]) {
    throw new Error(`Invalid Token Logo symbol: ${symbol}`);
  }
  return <img src={logosBySymbol[symbol]} alt={`${symbol} Logo`} width={size} height={size} />;
};

export default TokenSymbol;
