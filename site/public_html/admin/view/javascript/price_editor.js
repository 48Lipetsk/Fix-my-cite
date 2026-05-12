$(function(){
    $("body").find(".price_column").on("click", function(e){
        if(!$(e.target).hasClass(".price_editor") && $(e.target).closest(".price_editor").length <= 0){
            if ($(this).find(".price_editor").length > 0) {
                return;
            }
            addPriceEditor($(this), $(this).attr("data-price-val"));
        }
    });

    $("body").on("click", function(e){
        if($(e.target).attr("data-action") == 'closePriceEditor' || $(e.target).closest("div").attr("data-action") == 'closePriceEditor') {
            closePriceEditor();
        }
    });

    $("body").on("click", function(e){
        if(($(e.target).attr("data-action") == 'successPriceEditor' || $(e.target).closest("div").attr("data-action") == 'successPriceEditor') && $(e.target).closest(".price_column").length > 0) {
            successPriceEditor($(e.target).closest(".price_column"));
        }
    });

    $("body").on("click", function(e){
        if(!$(e.target).hasClass("price_column") && $(e.target).closest(".price_column").length <= 0){
            closePriceEditor();
        }
    });

    function addPriceEditor($this, val){
        var editor_html = '';
        editor_html += '<div class="price_editor" style="display: none">';
        editor_html += '    <input name="new_price" type="text" value="' + val + '" />';
        editor_html += '    <div class="price_editor__inner">';
        editor_html += '        <div class="price_editor__button btn btn-success" data-action="successPriceEditor">';
        editor_html += '            <i class="fa fa-check"></i>';
        editor_html += '        </div>';
        editor_html += '        <div class="price_editor__button btn btn-danger" data-action="closePriceEditor">';
        editor_html += '            <i class="fa fa-times"></i>';
        editor_html += '        </div>';
        editor_html += '    </div>';
        editor_html += '</div>';
        $this.prepend(editor_html);
        $this.find(".price_editor").fadeIn(100);
    }

    function successPriceEditor($elem){
        var new_price = $elem.find(".price_editor input[name='new_price']").val();
        var product_id = $elem.closest('tr').find('input[name="selected[]"]').val();
        var price_type = $elem.attr('data-price-type');
        var user_token = $elem.attr('data-user-token');

        $.ajax({
            url: "index.php?route=catalog/product/changePrice&user_token=" + user_token,
            type: 'post',
            data: {product_id: product_id, new_price: new_price, price_type: price_type},
            dataType: 'json',
            success: function(json){
                if(json['error']){
                    console.log(json['error']);
                }else if(json['success']) {
                    if(json['new_price']) {
                        $elem.attr('data-price-val', json['new_price_val']);
                        $elem.find(".pricing").html(json['new_price']);
                    }
                    closePriceEditor();
                }
            }
        });
    }

    function closePriceEditor(){
        $("body").find(".price_editor").fadeOut(100);
        setTimeout(function(){
            $("body").find(".price_editor").remove();
        }, 100);
    }
});