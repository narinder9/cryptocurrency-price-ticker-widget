<?php

/*
|--------------------------------------------------------------------------
| getting all coins details from database
|--------------------------------------------------------------------------
 */
function ccpw_get_coins_data($coin_id_arr)
{
    $DB = new ccpw_database;
    $coin_data = $DB->get_coins(array('coin_id' => $coin_id_arr, 'number' => '1000', 'orderby' => 'market_cap', 'order' => 'DESC'));

    if (is_array($coin_data) && isset($coin_data)) {
        $coin_rs_data = ccpw_objectToArray($coin_data);
        return $coin_rs_data;
    } else {
        return false;
    }

}

/*
|--------------------------------------------------------------------------
| getting all coins details from database
|--------------------------------------------------------------------------
 */

function ccpw_get_top_coins_data($limit)
{
    $order_col_name = 'market_cap';
    $order_type = 'DESC';
    $DB = new ccpw_database;
    $coin_data = $DB->get_coins(array("number" => $limit, 'offset' => 0, 'orderby' => $order_col_name,
        'order' => $order_type,
    ));
    if (is_array($coin_data) && isset($coin_data)) {
        $coins_rs_arr = ccpw_objectToArray($coin_data);
        return $coins_rs_arr;
    } else {
        return false;
    }
}

/*
|--------------------------------------------------------------------------
| getting all coin ids from database
|--------------------------------------------------------------------------
 */
function ccpw_get_all_coin_ids()
{
    $DB = new ccpw_database;
    $coin_data = $DB->get_coins(array('number' => '1000'));
    if (is_array($coin_data) && isset($coin_data)) {
        $coin_data = ccpw_objectToArray($coin_data);
        $coins = array();
        $api = get_option('ccpw_options');
        $api = (!isset($api['select_api']) && empty($api['select_api'])) ? "coin_gecko" : $api['select_api'];

        if ($api == "coin_gecko") {
            foreach ($coin_data as $coin) {
                $coins[$coin['coin_id']] = $coin['name'];
            }
        } else {
            foreach ($coin_data as $coin) {
                $coin_id = ccpw_coin_array($coin['coin_id']);

                $coins[$coin_id] = $coin['name'];
            }

        }
        return $coins;
    } else {
        return false;
    }

}

/**
 * Check if provided $value is empty or not.
 * Return $default if $value is empty
 */
function ccpw_set_default_if_empty($value, $default = 'N/A')
{
    return $value ? $value : $default;
}

/*
Adding coins SVG logos
 */
function ccpw_get_coin_logo($coin_id, $size = 32, $HTML = true)
{
    $logo_html = '';

    $api = get_option('ccpw_options');
    $api = (!isset($api['select_api']) && empty($api['select_api'])) ? "coin_gecko" : $api['select_api'];
    if ($api == "coin_gecko") {
        $coin_svg = CCPWF_DIR . '/assets/coin-logos/' . strtolower($coin_id) . '.svg';
        $coin_png = CCPWF_DIR . '/assets/coin-logos/' . strtolower($coin_id) . '.png';

        if (file_exists($coin_svg)) {
            $coin_svg = CCPWF_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.svg';
            if ($HTML == true) {
                $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_svg . '">';
            } else {
                $logo_html = $coin_svg;
            }
            return $logo_html;

        } else if (file_exists($coin_png)) {
            $coin_png = CCPWF_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.png';
            if ($HTML == true) {
                $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_png . '">';
            } else {
                $logo_html = $coin_png;
            }
            return $logo_html;

        } else {
            return false;
        }

    } else {

        $coin_id = ccpw_coin_array($coin_id);

        $coin_svg = CCPWF_DIR . '/assets/coin-logos/' . strtolower($coin_id) . '.svg';
        $coin_png = CCPWF_DIR . '/assets/coin-logos/' . strtolower($coin_id) . '.png';

        if (file_exists($coin_svg)) {
            $coin_svg = CCPWF_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.svg';
            if ($HTML == true) {
                $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_svg . '">';
            } else {
                $logo_html = $coin_svg;
            }
            return $logo_html;

        } else if (file_exists($coin_png)) {
            $coin_png = CCPWF_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.png';
            if ($HTML == true) {
                $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_png . '">';
            } else {
                $logo_html = $coin_png;
            }
            return $logo_html;

        } else {

            //$coin_id=ccpw_coin_array($coin_id, true);

            $coin_png = "https://static.coinpaprika.com/coin/$coin_id/logo.png";
            $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_png . '" width="' . $size . '">';
        }
        return $logo_html;

    }

    return $logo_path = CCPWF_URL . 'assets/images/default-logo.png';

    //return 'https://static.coinpaprika.com/coin/' . ccpws_coin_array($coin_id, true) . '/logo.png';

}
function ccpw_coin_array($coin_id, $flip = false)
{
    $coin_list = array(
        "btc-bitcoin" => "bitcoin",
        "eth-ethereum" => "ethereum",
        "usdt-tether" => "tether",
        "usdc-usd-coin" => "usd-coin",
        "bnb-binance-coin" => "binancecoin",
        "busd-binance-usd" => "binance-usd",
        "xrp-xrp" => "ripple",
        "ada-cardano" => "cardano",
        "sol-solana" => "solana",
        "doge-dogecoin" => "dogecoin",
        "dot-polkadot" => "polkadot",
        "shib-shiba-inu" => "shiba-inu",
        "dai-dai" => "dai",
        "steth-lido-staked-ether" => "staked-ether",
        "matic-polygon" => "matic-network",
        "trx-tron" => "tron",
        "avax-avalanche" => "avalanche-2",
        "wbtc-wrapped-bitcoin" => "wrapped-bitcoin",
        "leo-leo-token" => "leo-token",
        "etc-ethereum-classic" => "ethereum-classic",
        "okb-okb" => "okb",
        "ltc-litecoin" => "litecoin",
        "ftt-ftx-token" => "ftx-token",
        "atom-cosmos" => "cosmos",
        "link-chainlink" => "chainlink",
        "cro-cryptocom-chain" => "crypto-com-chain",
        "near-near-protocol" => "near",
        "uni-uniswap" => "uniswap",
        "xlm-stellar" => "stellar",
        "xmr-monero" => "monero",
        "bch-bitcoin-cash" => "bitcoin-cash",
        "algo-algorand" => "algorand",
        "flow-flow" => "flow",
        "xcn-chain" => "chain-2",
        "vet-vechain" => "vechain",
        "icp-internet-computer" => "internet-computer",
        "fil-filecoin" => "filecoin",
        "eos-eos" => "eos",
        "frax-frax" => "frax",
        "ape-apecoin" => "apecoin",
        "hbar-hedera-hashgraph" => "hedera-hashgraph",
        "sand-the-sandbox" => "the-sandbox",
        "mana-decentraland" => "decentraland",
        "xtz-tezos" => "tezos",
        "qnt-quant" => "quant-network",
        "axs-axie-infinity" => "axie-infinity",
        "egld-elrond" => "elrond-erd-2",
        "chz-chiliz" => "chiliz",
        "aave-new" => "aave",
        "theta-theta-token" => "theta-token",
        "lend-ethlend" => "aave",
        "tusd-trueusd" => "true-usd",
        "bsv-bitcoin-sv" => "bitcoin-cash-sv",
        "usdp-paxos-standard-token" => "paxos-standard",
        "ldo-lido-dao" => "lido-dao",
        "kcs-kucoin-token" => "kucoin-shares",
        "btt-bittorrent" => "bittorrent",
        "zec-zcash" => "zcash",
        "hbtc-huobi-btc" => "huobi-btc",
        "miota-iota" => "iota",
        "ht-huobi-token" => "huobi-token",
        "grt-the-graph" => "the-graph",
        "hnt-helium" => "helium",
        "usdd-usdd" => "usdd",
        "klay-klaytn" => "klay-token",
        "xec-ecash" => "ecash",
        "ftm-fantom" => "fantom",
        "mkr-maker" => "maker",
        "usdn-neutrino-usd" => "neutrino",
        "snx-synthetix-network-token" => "havven",
        "neo-neo" => "neo",
        "gt-gatechain-token" => "gatechain-token",
        "paxg-pax-gold" => "pax-gold",
        "rune-thorchain" => "thorchain",
        "bitdao" => "bit-bitdao",
        "ar-arweave" => "arweave",
        "zil-zilliqa" => "zilliqa",
        "cake-pancakeswap" => "pancakeswap-token",

        "nexo-nexo" => "nexo",
        "bat-basic-attention-token" => "basic-attention-token",
        "amp-amp" => "amp-token",
        "dash-dash" => "dash",
        "stx-stacks" => "blockstack",
        "enj-enjin-coin" => "enjincoin",
        "waves-waves" => "waves",
        "lrc-loopring" => "loopring",
        "xaut-tether-gold" => "tether-gold",
        "kava-kava" => "kava",
        "btg-bitcoin-gold" => "bitcoin-gold",
        "gmt-gomining-token" => "stepn",
        "crv-curve-dao-token" => "curve-dao-token",
        "ksm-kusama" => "kusama",
        "xem-nem" => "nem",
        "dcr-decred" => "decred",
        "twt-trust-wallet-token" => "trust-wallet-token",
        "gno-gnosis" => "gnosis",
        "mina-mina-protocol" => "mina-protocol",
        "1inch-1inch" => "1inch",
        "gala-gala" => "gala",
        "fxs-frax-share" => "frax-share",
        "xdc-xdc-network" => "xdce-crowd-sale",
        "celo-celo" => "celo",
        "cel-celsius" => "celsius-degree-token",
        "hot-holo" => "holotoken",
        "tfuel-theta-fuel" => "theta-fuel",
        "rpl-rocket-pool" => "rocket-pool",
        "cvx-convex-finance" => "convex-finance",
        "rvn-ravencoin" => "ravencoin",
        "qtum-qtum" => "qtum",
        "rose-oasis-network" => "oasis-network",
        "comp-compoundd" => "compound-governance-token",
        "gusd-gemini-dollar" => "gemini-dollar",
        "kda-kadena" => "kadena",
        "ens-ethereum-name-service" => "ethereum-name-service",
        "iost-iost" => "iostoken",
        "iotx-iotex" => "iotex",
        "ankr-ankr-network" => "ankr",
        "srm-serum" => "serum",
        "safemoon-safemoon" => "safemoon",
        "yfi-yearnfinance" => "yearn-finance",
        "lpt-livepeer" => "livepeer",
        "zel-zelcash" => "zelcash",
        "zrx-0x" => "0x",
        "omg-omg-network" => "omisego",
        "ust-terrausd" => "terrausd",
        "one-harmony" => "harmony",
        "jst-just" => "just",
        "glm-golem" => "golem",
        "rsr-reserve-rights" => "reserve-rights-token",
        "audio-audius" => "audius",
        "luna-terra-v2" => "terra-luna-2",
        "syn-synapse" => "synapse-2",
        "ln-link" => "link",
        "op-optimism" => "optimism",
        "sfm-safemoon" => "safemoon-2",
        "icx-icon" => "icon",
        "ont-ontology" => "ontology",
        "wax-wax" => "wax",
        "bal-balancer" => "balancer",
        "sushi-sushi" => "sushi",
        "nu-nucypher" => "nucypher",
        "scrt-secret" => "secret",
        "sc-siacoin" => "siacoin",
        "hive-hive" => "hive",
        "dydx-dydx" => "dydx",
        "zen-horizen" => "zencash",
        "mc-merit-circle" => "merit-circle",
        "babydoge-baby-doge-coin" => "baby-doge-coin",
        "dag-constellation" => "constellation-labs",
        "lusd-liquity-usd" => "liquity-usd",
        "knc-kyber-network" => "kyber-network-crystal",
        "xch-chia-" => "chia",
        "alusd-alchemixusd" => "alchemix-usd",
        "uma-uma" => "uma",
        "efyt-ergo" => "ergo",
        "sxp-swipe" => "swipe",
        "ewt-energy-web-token" => "energy-web-token",
        "skl-skale" => "skale",
        "mxc-machine-xchange-coin" => "mxc",
        "woo-wootrade" => "woo-network",
        "poly-polymath" => "polymath",
        "cspr-casper-network" => "casper-network",
        "nft-apenft" => "apenft",
        "chsb-swissborg" => "swissborg",
        "ethos-ethos" => "ethos",
        "dgb-digibyte" => "digibyte",
        "elon-dogelon-mars" => "dogelon-mars",
        "slp-smooth-love-potion" => "smooth-love-potion",
        "lsk-lisk" => "lisk",
        "pla-playdapp" => "playdapp",
        "rndr-render-token" => "render-token",
        "fei-fei-protocol" => "fei-usd",

        "fx-function-x" => "fx-coin",
        "spell-spell-token" => "spell-token",
        "cet-coinex-token" => "coinex-token",
        "ckb-nervos-network" => "nervos-network",
        "nest-nest-protocol" => "nest",
        "eurs-stasis-eurs" => "stasis-eurs",
        "raca-radio-caca" => "radio-caca",
        "ren-republic-protocol" => "republic-protocol",
        "people-constitutiondao" => "constitutiondao",
        "xno-nano" => "nano",
        "win-winklink" => "wink",
        "cvc-civic" => "civic",
        "orbs-orbs" => "orbs",
        "cfx-conflux-network" => "conflux-token",
        "med-medibloc-qrc20" => "medibloc",
        "pltc-platoncoin" => "platoncoin",
        "snt-status" => "status",
        "inj-injective-protocol" => "injective-protocol",
        "titan-titanswap" => "titanswap",
        "ardr-ardor" => "ardor",
        "nmr-numeraire" => "numeraire",
        "celr-celer-network" => "celer-network",
        "api3-api3" => "api3",
        "prom-prometeus" => "prometeus",
        "tribe-tribe" => "tribe-2",
        "coti-coti" => "coti",
        "mx-mx-token" => "mx-token",
        "tel-telcoin" => "telcoin",
        "dka-dkargo" => "dkargo",
        "btse-btse-token" => "btse-token",
        "xyo-xyo-network" => "xyo-network",
        "chr-chromia" => "chromaway",
        "bsw-biswap" => "biswap",
        "ygg-yield-guild-games" => "yield-guild-games",
        "mbox-mobox" => "mobox",
        "rlc-iexec-rlc" => "iexec-rlc",
        "trb-tellor" => "tellor",
        "bnt-bancor" => "bancor",
        "uos-ultra" => "ultra",
        "exrd-e-radix" => "e-radix",
        "powr-power-ledger" => "power-ledger",
        "sys-syscoin" => "syscoin",
        "dent-dent" => "dent",
        "steem-steem" => "steem",
        "wrx-wazirx" => "wazirx",
        "rad-radicle" => "radicle",
        "hxro-hxro" => "hxro",
        "susd-susd" => "nusd",
        "keep-keep-network" => "keep-network",
        "ogn-origin-protocol" => "origin-protocol",
        "ray-raydium" => "raydium",
        "strax-stratis" => "stratis",
        "vtho-vethor-token" => "vethor-token",
        "req-request-network" => "request-network",
        "c98-coin98" => "coin98",
        "fun-funfair" => "funfair",
        "trac-origintrail" => "origintrail",
        "rev-revain" => "revain",
        "arrr-pirate" => "pirate-chain",
        "husd-husd" => "husd",
        "xido-xido-finance" => "xido-finance",
        "storj-storj" => "storj",
        "aurora-aurora" => "aurora-near",
        "veri-veritaseum" => "veritaseum",
        "rbn-ribbon-finance" => "ribbon-finance",
        "maid-maidsafecoin" => "maidsafecoin",
        "xmon-xmon" => "xmon",
        "ufo-ufo-gaming" => "ufo-gaming",
        "mtl-metal" => "metal",
        "stpt-stpt" => "stp-network",
        "cdt-blox" => "blox",
        "tlm-alien-worlds" => "alien-worlds",
        "reef-reef" => "reef",
        "ctc-creditcoin" => "creditcoin-2",
        "ads-adshares" => "adshares",
        "mdx-mdex" => "mdex",
        "qkc-quarkchain" => "quark-chain",
        "ark-ark" => "ark",
        "stormx-stormx" => "storm",
        "sfund-seedifyfund" => "seedify-fund",
        "renbtc-renbtc" => "renbtc",
        "xvs-venus" => "venus",
        "ocean-ocean-protocol" => "ocean-protocol",
        "ach-alchemy-pay" => "alchemy-pay",
        "movr-moonriver" => "moonriver",
        "elf-aelf" => "aelf",
        "nkn-nkn" => "nkn",
        "klv-klever" => "klever",
        "iq-everipedia" => "everipedia",
        "meta-metadium" => "metadium",
        "strk-strike" => "strike",
        "ant-aragon" => "aragon",
        "deso-decentralized-social" => "bitclout",
        "santos-santos-fc-fan-token" => "santos-fc-fan-token",
        "asd-ascendex-token" => "asd",
        "badger-badger" => "badger-dao",
        "xsgd-xsgd" => "xsgd",
        "rep-augur" => "augur",
        "fetch-ai" => "fetch-ai",
        "ilv-illuvium" => "illuvium",
        "core-cvaultfinance" => "cvault-finance",
        "akt-akash-network" => "akash-network",
        "utk-utrust" => "utrust",
        "rif-rif-token" => "rif-token",
        "tlos-telos" => "telos",
        "wmt-world-mobile-token" => "world-mobile-token",
        "mft-hifi-finance" => "mainframe",
        "tt-thunder-token" => "thunder-token",
        "cusd-celo-dollar" => "celo-dollar",
        "band-band-protocol" => "band-protocol",
        "dusk-dusk-network" => "dusk-network",
        "aergo-aergo" => "aergo",
        "ampl-ampleforth" => "ampleforth",
        "vra-verasity" => "verasity",
        "kp3r-keep3rv1" => "keep3rv1",
        "xvg-verge" => "verge",
        "pols-polkastarter" => "polkastarter",
        "ousd-origin-dollar" => "origin-dollar",
        "perp-perpetual-protocol" => "perpetual-protocol",
        "mngo-mango-markets" => "mango-markets",
        "wozx-efforce" => "wozx",
        "aleph-alephim" => "aleph",
        "dero-dero" => "dero",
        "agix-singularitynet" => "singularitynet",
        "hero-metahero" => "metahero",
        "sero-super-zero" => "super-zero",
        "divi-divi" => "divi",
        "idex-idex" => "aurora-dao",
        "wnxm-wrapped-nxm" => "wrapped-nxm",
        "hunt-hunt" => "hunt-token",
        "tomo-tomochain" => "tomochain",
        "cocos-cocos-bcx" => "cocos-bcx",
        "ava-travala" => "concierge-io",
        "etn-electroneum" => "electroneum",
        "eps-ellipsis" => "ellipsis",
        "forth-ampleforth-governance-token" => "ampleforth-governance-token",
        "xpr-proton" => "proton",
        "usdk-usdk" => "usdk",
        "pha-phala-network" => "pha",
        "rise-everrise" => "everrise",
        "jasmy-jasmycoin" => "jasmycoin",
        "pro-propy" => "propy",
        "orn-orion-protocol" => "orion-protocol",
        "cult-cult-dao" => "cult-dao",
        "cre-carry" => "carry",
        "super-superfarm" => "superfarm",
        "alpaca-alpaca-finance" => "alpaca-finance",
        "starl-starlink" => "starlink",
        "xcad-xcad-network" => "xcad-network",
        "lazio-lazio-fan-token" => "lazio-fan-token",
        "wan-wanchain" => "wanchain",
        "hydra-hydra" => "hydra",
        "ela-elastos" => "elastos",
        "aioz-aioz-network" => "aioz-network",
        "time-chronotech" => "chronobank",
        "blz-bluzelle" => "bluzelle",
        "yfii-dfimoney" => "yfii-finance",
        "kmd-komodo" => "komodo",
        "bmx-bitmart-token" => "bitmart-token",
        "alcx-alchemix" => "alchemix",
        "mln-enzyme" => "melon",
        "samo-samoyedcoin" => "samoyedcoin",
        "arpa-arpa-chain" => "arpa-chain",
        "lcx-lcx" => "lcx",
        "gas-gas" => "gas",
        "moc-mossland" => "mossland",
        "onit-onbuff" => "onbuff",
        "dnt-district0x" => "district0x",
        "aqt-alpha-quark-token" => "alpha-quark-token",
        "rfr-refereum" => "refereum",
        "ramp-ramp" => "ramp",
        "lto-lto-network" => "lto-network",
        "rei-rei-network" => "rei-network",
        "sbd-steem-dollars" => "steem-dollars",
        "hns-handshake" => "handshake",
        "dpi-defi-pulse-index" => "defipulse-index",
        "atolo-rizon" => "rizon",
        "bifi-beefyfinance" => "beefy-finance",
        "ceur-celo-euro" => "celo-euro",
        "kar-karura" => "karura",
        "fct-firmachain" => "firmachain",
        "qrdo-qredo" => "qredo",
        "pre-presearch" => "presearch",
        "noia-syntropy" => "noia-network",
        "dia-dia" => "dia-data",
        "soul-phantasma" => "phantasma",
        "quick-quickswap" => "quick",
        "lever-leverfi" => "lever",
        "bcd-bitcoin-diamond" => "bitcoin-diamond",
        "ae-aeternity" => "aeternity",
        "rook-rook" => "rook",
        "htr-hathor-network" => "hathor",
        "dep-deapcoin" => "deapcoin",
        "coval-circuits-of-value" => "circuits-of-value",
        "anc-anchor-protocol" => "anchor-protocol",
        "rsv-reserve" => "reserve",
        "map-map-protocol" => "marcopolo",
        "hoo-hoo-token" => "hoo-token",
        "cxo-cargox" => "cargox",
        "farm-harvest-finance" => "harvest-finance",
        "bts-bitshares" => "bitshares",
        "fio-fio-protocol" => "fio-protocol",
        "iris-irisnet" => "iris-network",
        "lit-litentry" => "litentry",
        "agld-adventure-gold" => "adventure-gold",
        "grs-groestlcoin" => "groestlcoin",
        "fox-fox-token" => "shapeshift-fox-token",
        "ubt-unibright" => "unibright",
        "mintme-com-coin" => "webchain",
        "rari-rarible" => "rarible",
        "key-selfkey" => "selfkey",
        "ern-ethernity-chain" => "ethernity-chain",
        "sps-splintershards" => "splinterlands",
        "mir-mir-coin" => "mirror-protocol",
        "aog-smartofgiving" => "smartofgiving",
        "om-mantra-dao" => "mantra-dao",
        "apm-apm-coin" => "apm-coin",
        "ctxc-cortex" => "cortex",
        "hoge-hoge-finance" => "hoge-finance",
        "firo-firo" => "zcoin",
        "cos-contentos" => "contentos",
        "qom-shiba-predator" => "shiba-predator",
        "mv-gensokishi-metaverse" => "gensokishis-metaverse",
        "nct-polyswarm" => "polyswarm",
        "solve-solve" => "solve-care",
        "aion-aion" => "aion",
        "mix-mixmarvel" => "mixmarvel",
        "wild-wilder-world" => "wilder-world",
        "chess-tranchess" => "tranchess",
        "adx-adex" => "adex",
        "nwc-newscryptoio" => "newscrypto-coin",
        "upp-sentinel-protocol" => "sentinel-protocol",
        "ali-ailink-token" => "alethea-artificial-liquid-intelligence-token",
        "gene-genopets" => "genopets",
        "kin-kin" => "kin",
        "toke-tokemak" => "tokemak",
        "stc-student-coin" => "starcoin",
        "ddx-derivadao" => "derivadao",
        "beam-beam" => "beam",
        "nuls-nuls" => "nuls",
        "prq-parsiq" => "parsiq",
        "vai-vai" => "vai",
        "hi-hi-dollar" => "hi-dollar",
        "tnb-time-new-bank" => "time-new-bank",
        "apx-apollox-token" => "apollox-2",
        "idrt-rupiah-token" => "rupiah-token",
        "axel-axel" => "axel",
        "snm-sonm" => "sonm",
        "swap-trustswap" => "trustswap",
        "mith-mithril" => "mithril",
        "ult-ultiledger" => "ultiledger",
        "mbl-moviebloc" => "moviebloc",
        "sos-opendao" => "opendao",
        "wxt-wirex-token" => "wirex",
        "mona-monacoin" => "monavale",
        "snl-sport-and-leisure" => "sport-and-leisure",
        "wtc-waltonchain" => "waltonchain",
        "troy-troy" => "troy",
        "hex-hex" => "hex",
        "mct-metacraft" => "myconstant",
        "la-latoken" => "latoken",
        "pac-paccoin" => "paccoin",
        "zb-zb" => "zb-token",
        "safe-safe" => "safe-coin-2",

        "asm-assemble-protocol" => "as-monaco-fan-token",
        "egg-nestree" => "waves-ducks",
        "tnt-tierion" => "tierion",
        "snn-sechain" => "sechain",
        "hyn-hyperion" => "hyperion",
        "eum-elitium" => "elitium",
        "clt-coinloan" => "coinloan",
        "orc-orbit-chain" => "orclands-metaverse",
        "ong-ong" => "somee-social-old",
        "data-streamr-datacoin" => "data-economy-index",
        "alt-alitas" => "alt-estate",
        "btcb-binance-bitcoin" => "bitcoinbrand",
        "con-conun" => "paycon-token",
        "loom-loom-network" => "loom-network-new",
        "pit-pitbull" => "pitbull",
        "best-bitpanda-ecosystem-token" => "bitpanda-ecosystem-token",
        "wbnb-wrapped-bnb" => "wbnb",
        "bnx-binaryx" => "binaryx",
        "cfg-centrifuge" => "centrifuge",
        "xym-symbol" => "symbol",
        "hedg-hedgetrade" => "hedgetrade",
        "cennz-centrality" => "centrality",
        "frts-fruits" => "fruits",
        "aht-ahatoken" => "ahatoken",
        "burger-burger-swap" => "burger-swap",
        "btrst-braintrust" => "braintrust",
        "asm-assemble-protocol" => "assemble-protocol",
        "ccxx-counosx" => "counosx",
        "mines-of-dalarnia-dar" => "mines-of-dalarnia",

        "porto-fc-porto" => "fc-porto",
        "wrapped-terra" => "luna-terra",
        "ihc-inflation-hedging-coin" => "inflation-hedging-coin",
        "bnana-banana-token" => "banana-token",
        "btcv-bitcoin-vault" => "bitcoinv",
        "dx-dxchain-token" => "dxchain",
        "bora-bora" => "bora",
        "cbk-cobak-token" => "cobak-token",
        "msb-misbloc" => "misbloc",
        "xdag-dagger-by-xdag" => "dagger",

        "plc-platincoin" => "platincoin",
        "people-constitutiondao" => "constitutiondao-wormhole",
        "locus-locus-chain" => "locus-chain",
        "brg-bridge-oracle" => "bridge-oracle",
        "seele-seele" => "seele",
        "osmo-osmosis" => "osmosis",
        "asm-assemble-protocol" => "assemble-protocol",
    );

    if ($flip == true) {
        $fliped_array = array_flip($coin_list);
        return (isset($fliped_array[$coin_id])) ? $fliped_array[$coin_id] : $coin_id;
    } else {
        return (isset($coin_list[$coin_id])) ? $coin_list[$coin_id] : $coin_id;

    }

}

function ccpw_format_number($n)
{
    $formatted = $n;
    if ($n <= -1) {
        $formatted = number_format($n, 2, '.', ',');
    } else if ($n < 0.50) {
        $formatted = number_format($n, 6, '.', ',');
    } else {
        $formatted = number_format($n, 2, '.', ',');
    }
    return $formatted;
}

// object to array conversion
function ccpw_objectToArray($d)
{
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
         * Return array converted to object
         * Using __FUNCTION__ (Magic constant)
         * for recursive call
         */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
}

// currencies symbol
function ccpw_currency_symbol($name)
{
    $cc = strtoupper($name);
    $currency = array(
        "USD" => "&#36;", //U.S. Dollar
        "CLP" => "&#36;", //CLP Dollar
        "SGD" => "S&#36;", //Singapur dollar
        "AUD" => "&#36;", //Australian Dollar
        "BRL" => "R&#36;", //Brazilian Real
        "CAD" => "C&#36;", //Canadian Dollar
        "CZK" => "K&#269;", //Czech Koruna
        "DKK" => "kr", //Danish Krone
        "EUR" => "&euro;", //Euro
        "HKD" => "&#36", //Hong Kong Dollar
        "HUF" => "Ft", //Hungarian Forint
        "ILS" => "&#x20aa;", //Israeli New Sheqel
        "INR" => "&#8377;", //Indian Rupee
        "IDR" => "Rp", //Indian Rupee
        "KRW" => "&#8361;", //WON
        "CNY" => "&#165;", //CNY
        "JPY" => "&yen;", //Japanese Yen
        "MYR" => "RM", //Malaysian Ringgit
        "MXN" => "&#36;", //Mexican Peso
        "NOK" => "kr", //Norwegian Krone
        "NZD" => "&#36;", //New Zealand Dollar
        "PHP" => "&#x20b1;", //Philippine Peso
        "PLN" => "&#122;&#322;", //Polish Zloty
        "GBP" => "&pound;", //Pound Sterling
        "SEK" => "kr", //Swedish Krona
        "CHF" => "Fr", //Swiss Franc
        "TWD" => "NT&#36;", //Taiwan New Dollar
        "PKR" => "Rs", //Rs
        "THB" => "&#3647;", //Thai Baht
        "TRY" => "&#8378;", //Turkish Lira
        "ZAR" => "R", //zar
        "RUB" => "&#8381;", //rub
    );

    if (array_key_exists($cc, $currency)) {
        return $currency[$cc];
    }
}

/*
|--------------------------------------------------------------------------
|  check admin side post type page
|--------------------------------------------------------------------------
 */
function ccpw_get_post_type_page()
{
    global $post, $typenow, $current_screen;

    if ($post && $post->post_type) {
        return $post->post_type;
    } elseif ($typenow) {
        return $typenow;
    } elseif ($current_screen && $current_screen->post_type) {
        return $current_screen->post_type;
    } elseif (isset($_REQUEST['page'])) {
        return sanitize_key($_REQUEST['page']);
    } elseif (isset($_REQUEST['post_type'])) {
        return sanitize_key($_REQUEST['post_type']);
    } elseif (isset($_REQUEST['post'])) {
        return get_post_type(sanitize_text_field($_REQUEST['post']));
    }
    return null;
}

function display_live_preview()
{
    $output = '';
    if (isset($_REQUEST['post']) && !is_array($_REQUEST['post'])) {

        $id = sanitize_text_field($_REQUEST['post']);

        $type = get_post_meta($id, 'type', true);
        $output = '<p><strong class="micon-info-circled"></strong>' . __('Backend preview may be a little bit different from frontend / actual view. Add this shortcode on any page for frontend view - ', 'ccpwx') . '<code>[ccpw id=' . $id . ']</code></p>' . do_shortcode("[ccpw id='" . $id . "']");
        $output .= '<script type="text/javascript">
         jQuery(document).ready(function($){
           $(".ccpw-ticker-cont").fadeIn();
         });
         </script>
         <style type="text/css">
         .ccpw-footer-ticker-fixedbar, .ccpw-header-ticker-fixedbar{
           position:relative!important;
         }
         .tickercontainer li{
             float:left!important;
             width:auto!important;
         }
         .ccpw-container-rss-view ul li.ccpw-news {
          margin-bottom: 30px;
          float: none;
          width: auto;
      }
      .ccpw-news-ticker .tickercontainer li{
        width: auto!important;
      }
         </style>';
        return $output;

    } else {
        return $output = '<h4><strong class="micon-info-circled"></strong> ' . __('Publish to preview the widget.', 'ccpwx') . '</h4>';

    }
}

function update_tbl_settings($post_id)
{
    $old_settings = get_post_meta($post_id, 'display_currencies_for_table', true);

    if ($old_settings) {
        switch ($old_settings) {
            case 'top-10':
                $newVal = 10;
                break;
            case 'top-50':
                $newVal = 50;
                break;
            case 'top-100':
                $newVal = 100;
                break;
            case 'top-200':
                $newVal = 200;
                break;
            case 'all':
                $newVal = 250;
                break;
            default:
                $newVal = 10;
        }
        update_post_meta($post_id, 'show-coins', $newVal);
        delete_post_meta($post_id, 'display_currencies_for_table');
    }
}

function ccpw_set_checkbox_default_for_new_post($default)
{
    return isset($_GET['post']) ? '' : ($default ? (string) $default : '');
}

function ccpw_value_format_number($n)
{

    if ($n <= 0.00001 && $n > 0) {
        return $formatted = number_format($n, 8, '.', ',');
    } else if ($n <= 0.0001 && $n > 0.00001) {
        return $formatted = number_format($n, 6, '.', ',');
    } else if ($n <= 0.001 && $n > 0.0001) {
        return $formatted = number_format($n, 5, '.', ',');
    } else if ($n <= 0.01 && $n > 0.001) {
        return $formatted = number_format($n, 4, '.', ',');
    } else if ($n <= 1 && $n > 0.01) {
        return $formatted = number_format($n, 3, '.', ',');
    } else {
        return $formatted = number_format($n, 2, '.', ',');
    }
}

function ccpw_format_coin_value($value, $precision = 2)
{
    if ($value < 1000000) {
        // Anything less than a million
        $formated_str = number_format($value, $precision);
    } else if ($value < 1000000000) {
        // Anything less than a billion
        $formated_str = number_format($value / 1000000, $precision) . 'M';
    } else {
        // At least a billion
        $formated_str = number_format($value / 1000000000, $precision) . 'B';
    }

    return $formated_str;
}

function ccpw_widget_format_coin_value($value, $precision = 2)
{
    if ($value < 1000000) {
        // Anything less than a million
        $formated_str = number_format($value, $precision);
    } else if ($value < 1000000000) {
        // Anything less than a billion
        $formated_str = number_format($value / 1000000, $precision) . ' Million';
    } else {
        // At least a billion
        $formated_str = number_format($value / 1000000000, $precision) . ' Billion';
    }

    return $formated_str;
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function ccpw_get_option($key = '', $default = false)
{
    if (function_exists('cmb2_get_option')) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option('ccpw_widget_settings', $key, $default);
    }

    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option('ccpw_widget_settings', $default);

    $val = $default;

    if ('all' == $key) {
        $val = $opts;
    } elseif (is_array($opts) && array_key_exists($key, $opts) && false !== $opts[$key]) {
        $val = $opts[$key];
    }

    return $val;
}
