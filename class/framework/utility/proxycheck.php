<?php
/**
 * Contains definition of ProxyCheck class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2018-2020 Newcastle University
 * @package Framework
 * @subpackage Utility
 */
     namespace Framework\Utility;

/**
 * A class that talk to proxycheck.io in order to get information about IP addresses
 *
 * You need an API key to use the system
 *
 * @link http://proxycheck.io/
 * @psalm-suppress PossiblyUnusedMethod
 */
    class ProxyCheck
    {
        private const PCURL = 'https://proxycheck.io/v2/';
/**
 * Check an IP address
 *
 * @param string            $key      Your API key
 * @param string            $ip       The IP address to check
 * @param array<string>     $options  Options for the check - see proxycheck.io API definition
 * @param ?string           $tag      A tag to identify this call.
 *
 * @return array<string>
 */
        public static function check(string $key, string $ip, array $options, ?string $tag = NULL) : array
        {
            // Check if they have enabled the blocked country feature by providing countries.
            if (!empty($options['countries']))
            {
                $options['asn'] = 1;
            }

            $query = ['key='.$key, 'port=1', 'seen=1'];
            foreach (['days', 'vpn', 'inf', 'asn'] as $fld)
            {
                if (isset($options[$fld]))
                {
                    $query[] = $fld.'='.$options[$fld];
                }
            }
/*
 * Now use curl to talk to proxycheck.io
 */
            $ch = curl_init(self::PCURL.$ip.'?'.implode('&', $query));
            $curlopts = [
                CURLOPT_CONNECTTIMEOUT  => 30,
                CURLOPT_POST            => 1,
                CURLOPT_POSTFIELDS      => 'tag='.urlencode($tag ?? \Config\Config::SITENAME),
                CURLOPT_RETURNTRANSFER  => TRUE,
            ];
            curl_setopt_array($ch, $curlopts);
            $json = curl_exec($ch);
            if ($json === FALSE)
            {
                $res = ['status' => 'error', 'message' => curl_error($ch)];
            }
            else
            {
                /** @psalm-suppress InvalidScalarArgument */
                $res = json_decode($json, TRUE);
                $res['block'] = FALSE;
                $res['reason'] = '';

                if (isset($res[$ip]['proxy']))
                { // this is a proxy server
                    if ($res[$ip]['proxy'] == 'yes')
                    {
                        $res['block'] = TRUE;
                        $res['reason'] = ($res[$ip]['type'] == 'VPN' ? 'vpn' : 'proxy');
                    }

                    if (!empty($options['countries']) && in_array($res[$ip]['isocode'], $options['countries']))
                    {
                        $res['block'] = TRUE;
                        $res['block_reason'] = 'country';
                    }
                }
            }
            curl_close($ch);
            return $res;
        }
    }
