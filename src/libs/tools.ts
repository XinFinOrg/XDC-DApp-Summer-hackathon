import mime from "mime-types"
//copy string to clipboard
export const clickToCopy = (content:string) => {
	const textarea = document.createElement('textarea');

	textarea.readOnly = true;
	textarea.style.position = 'absolute';
	textarea.style.left = '-9999px';

	textarea.value = content;

	document.body.appendChild(textarea);

	textarea.select();

	const result = document.execCommand('Copy');

	document.body.removeChild(textarea);

	return result;
}

//short content string
export const shortString = (content:string) => {
  if(content.length <= 17){
    return content;
  }

  const length = content.length;

  return content.substr(0,8)+"..."+content.substr(length-6,length);
}

//get file type
export const fileType = (name: string) => {
	const mimeType = mime.contentType(mime.lookup(name) || "application/octet-stream");

	return mimeType as string;
}

//get file size for human
export const fileSize = (size: number) => {
	if (size < 1024) {
		return size.toFixed(2) + ' B';
	} else {
		size /= 1024;
	}

	if (size < 1024) {
		return size.toFixed(2) + 'KB';
	} else {
		size /= 1024;
	}

	if (size < 1024) {
		return size.toFixed(2) + 'MB';
	} else {
		size /= 1024;
	}

	return size.toFixed(2) + 'GB'
}

//sleep a little while
export const sleep = async (time:number) => {
  return new Promise((resolve) => setTimeout(resolve, time));
}

//get url paramter
export const getUrlParamter = (name:string) => {
	const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
  const r = (window as any).location.search.substr(1).match(reg);
  if (r != null) {
    return unescape(r[2]);
  }
  return '';
}

//set url patamter
export const setUrlParamter = (name:string, value:string) => {
  const oldUrl = (window as any).location.href.toString();
  const re = new RegExp(name + '=[^&]*', 'gi');
  const paramter = name + '=' + value;
  let newUrl = oldUrl.replace(re, paramter);

  if(newUrl.indexOf(name + '=') === -1 ){
    if(newUrl.indexOf('?') === -1){
      newUrl = newUrl + '?' + paramter;
    }else{
      newUrl = newUrl + '&' + paramter;
    }
  }

  (window as any).history.pushState(null, null, newUrl);
}

//make file object
export const makeFileObject = (filename:string, fileType:string, content:string) => {
  const blob = new Blob([content], { type: fileType })

  const file = new File([blob], filename);

  const rawFile = {
    name: file.name,
    size: file.size,
    type: file.type,
    webkitRelativePath: file.webkitRelativePath,
    text: file.text,
    stream: file.stream,
    slice: file.slice,
    arrayBuffer: file.arrayBuffer,
  };

  return rawFile;
}