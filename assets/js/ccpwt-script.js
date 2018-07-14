jQuery(document).ready(function($){
		$(".multi-currency-tab li").on("click",function(){
		$(".multi-currency-tab li").removeClass("active-tab");
		$(this).addClass("active-tab");
		var active_curr=$(this).data("currency");
			$(".multi-currency-tab-content li").find('.mtab_price').each(function(){
				var price=$(this).data(active_curr);
				$(this).html(price);
			});
		});
	});