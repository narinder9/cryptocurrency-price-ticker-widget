jQuery(document).ready(function($){
    var table_id = '';
$.fn.ccpwDatatable = function () {
    table_id = $(this).attr('id');
    var $ccpw_table = $(this);
    var columns = [];
    var rtype = $ccpw_table.data('rtype');
    var coinList = $ccpw_table.data('coin-list');

    var fiatSymbol = $ccpw_table.data('currency-symbol');
    var fiatCurrencyRate = $ccpw_table.data('currency-rate');
    var pagination = $ccpw_table.data('pagination');
    var fiatCurrency = $ccpw_table.data('currency-type');
    var requiredCurrencies = $ccpw_table.data('required-currencies');
    var prevtext= $ccpw_table.data("prev-coins");
    var nexttext = $ccpw_table.data("next-coins");
    var zeroRecords = $ccpw_table.data("zero-records");
    var currencyLink = $ccpw_table.data("currency-slug");
    var dynamicLink = $ccpw_table.data("dynamic-link");
    var loadingLbl = $ccpw_table.data("loadinglbl");
    var numberFormat = $ccpw_table.data("number-formating");
     $ccpw_table.find('thead th').each(function (index) {
         var thisTH=$(this);
        var index = thisTH.data('index');
        var classes = thisTH.data('classes');

        columns.push({
            data: index,
            name: index,
            render: function (data, type, row, meta) {
                
                if (meta.settings.json === undefined) { return data; }
                switch (index) {
                    case 'rank':
                        return data;
                    break;
                    case 'name':
                        if(typeof dynamicLink !='undefined' && dynamicLink!=""){
                            var coinLink = currencyLink+'/'+row.symbol+'/'+row.id;
                            var html = '<div class="'+classes+'"><a class="ccpw_links" title="'+row.name+'" href="'+coinLink+'"><span class="ccpw_coin_logo">'+row.logo+'</span><span class="ccpw_coin_symbol">('+row.symbol+')</span><br/><span class="ccpw_coin_name ccpw-desktop">'+row.name+'</span></a></div>';
                        }else{
                            var html = '<div class="'+classes+'"><span class="ccpw_coin_logo">'+row.logo+'</span><span class="ccpw_coin_symbol">('+row.symbol+')</span><br/><span class="ccpw_coin_name ccpw-desktop">'+data+'</span></div>';
                        }
                        return html;
                    case 'price':
                        if (typeof data !== 'undefined' && data !=null){
                            var formatedVal = ccpw_numeral_formating(data);
                            return html = '<div data-val="'+row.price+'" class="'+classes+'"><span class="ccpw-formatted-price">'+fiatSymbol + formatedVal+'</span></div>';
                     }else{
                            return html = '<div class="'+classes+'>?</div>';
                       }
                        break;
                    case 'change_percentage_24h':
                        if (typeof data !== 'undefined' && data != null) {
                        var changesCls = "up";
                            var wrpchangesCls = "ccpw-up";
                            if (typeof Math.sign === 'undefined') { Math.sign = function (x) { return x > 0 ? 1 : x < 0 ? -1 : x; } }
                        if (Math.sign(data) == -1) {
                            var changesCls = "down";
                            var wrpchangesCls = "ccpw-down";
                        }
                        var html = '<div class="'+classes + ' ' + wrpchangesCls+'"><span class="changes '+changesCls+'"><i class="ccpw_icon-'+changesCls+'" aria-hidden="true"></i>'+data+'%</span></div>';
                        return html;
                    }else{
                          return html='<div class="'+classes+'">?</span></div>';
                    }
                    break;
                    case 'market_cap':
                    if (typeof data !== 'undefined' && data !=null){
                        var formatedVal = ccpw_numeral_formating(data);
                        if(numberFormat){
                            var formatedVal = numeral(data).format('(0.00 a)').toUpperCase();
                        }
                        return html = '<div data-val="'+row.market_cap+'" class="'+classes+'"><span class="ccpw-formatted-market-cap">'+fiatSymbol + formatedVal+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'>?</div>';
                    }
                    break;
                    case 'total_volume':
                    if (typeof data !== 'undefined' && data !=null && data !="0.00"){
                       // console.log(data);
                        var formatedVal = ccpw_numeral_formating(data);
                        if(numberFormat){
                            var formatedVal = numeral(data).format('(0.00 a)').toUpperCase();
                        }
                        return html = '<div data-val="'+row.total_volume+'" class="'+classes+'"><span class="ccpw-formatted-total-volume">' + fiatSymbol + formatedVal+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'">?</div>';

                        
                    }
                    break;
                    case 'supply':
                    if (typeof data !== 'undefined' && data !=null  && row.supply!='N/A'){
                        var formatedVal =  ccpw_numeral_formating(data);
                        if(numberFormat){
                            var formatedVal = numeral(data).format('(0.00 a)').toUpperCase();
                        }
                        return html = '<div data-val="'+row.supply+'" class="'+classes+'"><span class="ccpw-formatted-supply">' + formatedVal+' '+row.symbol+'</span></div>';
                    }else{
                        return html = '<div class="'+classes+'">N/A</div>';
                    }
                    break;

                    
                    
                    default:
                        return data;
                }
            },
            "createdCell": function (td, cellData, rowData, row, col) {
                    $(td).attr('data-sort', cellData);
            } 
        });
    });
    
        $ccpw_table.DataTable({
            "deferRender": true,
            "serverSide": true,
            "ajax": {
                "url": ccpw_js_objects.ajax_url,
                "type": "POST",
                "dataType": "JSON",
                "data": function (d) {
                    d.action = "ccpw_get_coins_list",
                    d.nonce=ccpw_js_objects.wp_nonce,
                    d.currency =fiatCurrency,
                    d.currencyRate = fiatCurrencyRate,
                    d.requiredCurrencies = requiredCurrencies,
                    d.rtype=rtype,
                    d.coinslist=coinList
                    // etc
                },
              
                "error": function (xhr, error, thrown) {
                    alert('Something wrong with Server');
                }
            },
            "ordering": false,
            "searching": false,
            "pageLength":pagination,
            "columns": columns,
            "responsive": true,
            "lengthChange": false,
            "pagingType": "simple",
            "processing": true,
            "dom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
            "language": {
                "processing":loadingLbl,
                "loadingRecords":loadingLbl,
                "paginate": {
                    "next":  nexttext,
                    "previous":prevtext
                },
            },
            "zeroRecords":zeroRecords,
            "emptyTable":zeroRecords,
            "renderer": {
                "header": "bootstrap",
            },
            "drawCallback": function (settings) {
                $ccpw_table.tableHeadFixer({
                    // fix table header
                    head: true,
                    // fix table footer
                    foot: false,
                    left:2,
                    right:false,
                    'z-index':1
                    }); 
                    
            },
          
        });
    
    }

    $('.ccpw_table_widget').each(function(){
        $(this).ccpwDatatable();
    });

    if(table_id){
        new Tablesort(document.getElementById(table_id), {
            descending: true
        });
    }

   

    function ccpw_numeral_formating(data){
        if (data >= 25 || data <=-1) {
            var formatedVal = numeral(data).format('0,0.00');
        } else if (data >= 0.50 && data < 25) {
            var formatedVal = numeral(data).format('0,0.000');
        } else if (data >= 0.01 && data < 0.50) {
            var formatedVal = numeral(data).format('0,0.0000');
        } else if (data >= 0.0001 && data < 0.01) {
            var formatedVal = numeral(data).format('0,0.00000');
        } else {
            var formatedVal = numeral(data).format('0,0.00000000');
        } 
        return formatedVal;
    }

});