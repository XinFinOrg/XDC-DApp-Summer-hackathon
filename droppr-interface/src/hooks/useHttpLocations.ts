import uriToHttp from 'lib/utils/uriToHttp'
import { useMemo } from 'react'

export default function useHttpLocations(uri: string | undefined): string[] {
  // const ens = useMemo(() => (uri ? parseENSAddress(uri) : undefined), [uri])
  // const resolvedContentHash = useENSContentHash(ens?.ensName)
  return useMemo(() => {
    return uri ? uriToHttp(uri) : []
  }, [uri])
}
