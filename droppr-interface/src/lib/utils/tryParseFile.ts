import Papa from 'papaparse'

export function tryParseCsvFile(file: any, setData: (data: any) => void): void {
  try {
    Papa.parse(file, {
      complete: (parsed) => {
        setData(parsed.data)
      },
    })
  } catch (e) {
    console.error(e)
  }
}

export function tryParseTxtFile(file: any, setData: (data: any) => void): void {
  try {
    Papa.parse(file, {
      complete: (parsed) => {
        setData(parsed.data)
      },
    })
  } catch (e) {
    console.error(e)
  }
}

export function tryParseFile(file: any, fileType: string, setData: (data: any) => void): void {
  switch (fileType.toLowerCase()) {
    case 'text/csv':
      return tryParseCsvFile(file, setData)
    case 'text/plain':
      return tryParseTxtFile(file, setData)
  }
}
