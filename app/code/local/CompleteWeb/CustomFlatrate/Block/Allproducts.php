<?php
/**
 * @copyright   Copyright (c) 2010 Amasty
 */ 
class CompleteWeb_CustomFlatrate_Block_Allproducts extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	protected $_dummyElement;
	protected $_fieldRenderer;
	protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		//$html = $this->_getHeaderHtml($element);
		
		return '
		<style type="text/css">
			.filter select { width:auto !important; }
			td { white-space:nowrap !important; }
		</style>
		<script type="text/javascript" src="https://code.jquery.com/jquery-1.7.min.js"></script>
		<script>jQuery.noConflict()</script>
		<script language="javascript">
		
			

getProductChooser = function (url) {


    new Ajax.Request(
        url, {
            method: "post",
            onSuccess: function (b) {
                var a = $("product_chooser");
                a.update(b.responseText);
                a.scrollTo()
            }
        });
		
		
        
          
              
};

var VarienRulesForm = new Class.create();
VarienRulesForm.prototype = {
    initialize: function (a) {
        this.newChildUrl = a;
        this.shownElement = null;     
        this.chooserSelectedItems = $H({})
    },
    initParam: function (b) {
        b.rulesObject = this;
        var d = Element.down(b, ".label");
        if (d) {
            Event.observe(d, "click", this.showParamInputField.bind(this, b))
        }
        var f = Element.down(b, ".element");
        if (f) {
            var e = f.down(".rule-chooser-trigger");
            if (e) {
                Event.observe(e, "click", this.toggleChooser.bind(this, b))
            }
            var c = f.down(".rule-param-apply");
            if (c) {
                Event.observe(c, "click", this.hideParamInputField.bind(this, b))
            } else {
                f = f.down();
                if (!f.multiple) {
                    Event.observe(f, "change", this.hideParamInputField.bind(this, b))
                }
                Event.observe(f, "blur", this.hideParamInputField.bind(this, b))
            }
        }
        var a = Element.down(b, ".rule-param-remove");
        if (a) {
            Event.observe(a, "click", this.removeRuleEntry.bind(this, b))
        }
    },
    showChooserElement: function (c) {
        this.chooserSelectedItems = $H({});
        var a = this.updateElement.value.split(","),
        b = "";
        for (i = 0; i < a.length; i++) {
            b = a[i].strip();
            if (b != "") {
                this.chooserSelectedItems.set(b, 1)
            }
        }
        new Ajax.Updater(c, c.getAttribute("url"), {
            evalScripts: true,
            parameters: {
                form_key: FORM_KEY,
                "selected[]": this.chooserSelectedItems.keys()
            },
            onSuccess: this._processSuccess.bind(this) && this.showChooserLoaded.bind(this, c),
            onFailure: this._processFailure.bind(this)
        })
    },
    showChooserLoaded: function (a, b) {
        a.style.display = "block"
    },
    showChooser: function (a, c) {
        var b = a.up("li");
        if (!b) {
            return
        }
        b = b.down(".rule-chooser");
        if (!b) {
            return
        }
        this.showChooserElement(b)
    },
    hideChooser: function (a, c) {
        var b = a.up("li");
        if (!b) {
            return
        }
        b = b.down(".rule-chooser");
        if (!b) {
            return
        }
        b.style.display = "none"
    },
    toggleChooser: function (a, c) {
        var b = a.up("li").down(".rule-chooser");
        if (!b) {
            return
        }
        if (b.style.display == "block") {
            b.style.display = "none";
            this.cleanChooser(a, c)
        } else {
            this.showChooserElement(b)
        }
    },
    cleanChooser: function (a, c) {
        var b = a.up("li").down(".rule-chooser");
        if (!b) {
            return
        }
        b.innerHTML = ""
    },
    showParamInputField: function (a, c) {
        if (this.shownElement) {
            this.hideParamInputField(this.shownElement, c)
        }
        Element.addClassName(a, "rule-param-edit");
        var d = Element.down(a, ".element");
        var b = Element.down(d, "input.input-text");
        if (b) {
            b.focus();
            if (b && b.id && b.id.match(/__value$/)) {
                this.updateElement = b
            }
        }
        var b = Element.down(d, "select");
        if (b) {
            b.focus()
        }
        this.shownElement = a
    },
    hideParamInputField: function (a, d) {
        Element.removeClassName(a, "rule-param-edit");
        var b = Element.down(a, ".label"),
        c;
        if (!a.hasClassName("rule-param-new-child")) {
            c = Element.down(a, "select");
            if (c && c.options) {
                var f = [];
                for (i = 0; i < c.options.length; i++) {
                    if (c.options[i].selected) {
                        f.push(c.options[i].text)
                    }
                }
                var e = f.join(", ");
                b.innerHTML = e != "" ? e : "..."
            }
            c = Element.down(a, "input.input-text");
            if (c) {
                var e = c.value.replace(/(^\s+|\s+$)/g, "");
                c.value = e;
                if (e == "") {
                    e = "..."
                } else {
                    if (e.length > 30) {
                        e = e.substr(0, 30) + "..."
                    }
                }
                b.innerHTML = e
            }
        } else {
            c = Element.down(a, "select");
            if (c.value) {
                this.addRuleNewChild(c)
            }
            c.value = ""
        }
        if (c && c.id && c.id.match(/__value$/)) {
            this.hideChooser(a, d);
            this.updateElement = null
        }
        this.shownElement = null
    },
    addRuleNewChild: function (b) {
        var f = b.id.replace(/^.*__(.*)__.*$/, "$1");
        var h = $(b.id.replace(/__/g, ":").replace(/[^:]*$/, "children").replace(/:/g, "__"));
        var d = 0,
        c;
        var a = Selector.findChildElements(h, $A(["input.hidden"]));
        if (a.length) {
            a.each(function (k) {
                if (k.id.match(/__type$/)) {
                    c = 1 * k.id.replace(/^.*__.*?([0-9]+)__.*$/, "$1");
                    d = c > d ? c : d
                }
            })
        }
        var g = f + "--" + (d + 1);
        var j = b.value;
        var e = document.createElement("LI");
        e.className = "rule-param-wait";
        e.innerHTML = Translator.translate("Please wait, loading...");
        h.insertBefore(e, $(b).up("li"));
        new Ajax.Updater(e, this.newChildUrl, {
            evalScripts: true,
            parameters: {
                form_key: FORM_KEY,
                type: j.replace("/", "-"),
                id: g
            },
            onComplete: this.onAddNewChildComplete.bind(this, e),
            onSuccess: this._processSuccess.bind(this),
            onFailure: this._processFailure.bind(this)
        })
    },
    _processSuccess: function (b) {
        var a = b.responseText.evalJSON();
        if (a.ajaxExpired && a.ajaxRedirect) {
            alert(Translator.translate("Your session has been expired, you will be relogged in now."));
            location.href = a.ajaxRedirect
        }
        return true
    },
    _processFailure: function (a) {
        location.href = BASE_URL
    },
    onAddNewChildComplete: function (c) {
        $(c).removeClassName("rule-param-wait");
        var a = c.getElementsByClassName("rule-param");
        for (var b = 0; b < a.length; b++) {
            this.initParam(a[b])
        }
    },
    removeRuleEntry: function (b, c) {
        var a = Element.up(b, "li");
        a.parentNode.removeChild(a)
    },
    chooserGridInit: function (a) {},
    chooserGridRowInit: function (a, b) {
        if (!a.reloadParams) {
            a.reloadParams = {
                "selected[]": this.chooserSelectedItems.keys()
            }
        }
    },
    chooserGridRowClick: function (b, d) {
       
        
        var f = Event.findElement(d, "tr");
        var a = Event.element(d).tagName == "INPUT";
        if (f) {
            var e = Element.select(f, "input");
            if (e[0]) {
                var c = a ? e[0].checked : !e[0].checked;
                b.setCheckboxChecked(e[0], c)
            }
        }
        
    },
    chooserGridCheckboxCheck: function (b, a, c) {  
      
        if (c) {
            if (!a.up("th")) {
                this.chooserSelectedItems.set(a.value, 1)
            }
        } else {
            this.chooserSelectedItems.unset(a.value)
        }
                
        b.reloadParams = {
            "selected[]": this.chooserSelectedItems.keys()
        };
        
        $("carriers_completeweb_customflatrate_product_sku").value = this.chooserSelectedItems.keys().join(", ");           
        
        
      
       
    }
    
   
};


var rule_conditions_fieldset = new VarienRulesForm(\'\');

		jQuery(document).ready(function($) {
			$(\'#carriers_completeweb_customflatrate_product_sku\').after("<input id=\"trigger\" name=\"trigger\" type=\"button\" value=\"Choose Products\" class=\"rule-chooser-trigger\" onclick=\"getProductChooser(\'' . Mage::getUrl(
'adminhtml/promo_widget/chooser/attribute/sku/form/rule_conditions_fieldset',
array('_secure' => Mage::app()->getStore()->isAdminUrlSecure())
) . '?isAjax=true\'); return false;\" >&nbsp;&nbsp;<input id=\"trigger_close\" name=\"trigger_close\" type=\"button\" value=\"Close Products Chooser\" >").after(\'<div class="fieldset" id="product_chooser"></div>\');
			
			$(\'#carriers_completeweb_customflatrate_type\').change(function() {
				var carrier_type = $(\'#carriers_completeweb_customflatrate_type\').val();
				if(carrier_type == \'product\') {
					$(\'#row_carriers_completeweb_customflatrate_categories\').hide();
					$(\'#row_carriers_completeweb_customflatrate_product_sku\').show();
					$(\'#product_chooser\').show();
					$(\'#trigger\').show();
					$(\'#trigger_close\').show();
				} else if (carrier_type == \'category\') {
					$(\'#row_carriers_completeweb_customflatrate_categories\').show();
					$(\'#row_carriers_completeweb_customflatrate_product_sku\').hide();
					$(\'#product_chooser\').hide();
					$(\'#trigger\').hide();
					$(\'#trigger_close\').hide();
				} else {
					$(\'#row_carriers_completeweb_customflatrate_categories\').hide();
					$(\'#row_carriers_completeweb_customflatrate_product_sku\').hide();
					$(\'#product_chooser\').hide();
					$(\'#trigger\').hide();
					$(\'#trigger_close\').hide();
				}
			});
			$(\'#carriers_completeweb_customflatrate_type\').trigger(\'change\');
			
			$("#trigger_close").click(function() {
				
				$(\'#product_chooser\').html(\'\');
			});
		});

		</script> 
		';
        
    }

    protected function _getFieldRenderer()
    {
    	if (empty($this->_fieldRenderer)) {
    		$this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
    	}
    	return $this->_fieldRenderer;
    }

	protected function _getFieldHtml($fieldset, $moduleCode)
    {
		$currentVer = Mage::getConfig()->getModuleConfig($moduleCode)->version;
		if (!$currentVer)
            return '';
		  
		$moduleName = substr($moduleCode, strpos($moduleCode, '_') + 1); // in case we have no data in the RSS 
		
		$allExtensions = unserialize(Mage::app()->loadCache('ambase_extensions'));
            
        $status = '<a  target="_blank"><img src="'.$this->getSkinUrl('images/ambase/ok.gif').'" title="'.$this->__("Installed").'"/></a>';

		if ($allExtensions && isset($allExtensions[$moduleCode])){
            $ext = $allExtensions[$moduleCode];
            
            $url     = $ext['url'];
            $name    = $ext['name'];
            $lastVer = $ext['version'];

            $moduleName = '<a href="'.$url.'" target="_blank" title="'.$name.'">'.$name."</a>";

            if ($this->_convertVersion($currentVer) < $this->_convertVersion($lastVer)){
                $status = '<a href="'.$url.'" target="_blank"><img src="'.$this->getSkinUrl('images/ambase/update.gif').'" alt="'.$this->__("Update available").'" title="'.$this->__("Update available").'"/></a>';
            }
        }
        
        //TODO check if module output disabled in future

        $moduleName = $status . ' ' . $moduleName;
	
		$field = $fieldset->addField($moduleCode, 'label', array(
            'name'  => 'dummy',
            'label' => $moduleName,
            'value' => $currentVer,
		))->setRenderer($this->_getFieldRenderer());
			
		return $field->toHtml();
    }
    
    protected function _convertVersion($v)
    {
		$digits = @explode(".", $v);
		$version = 0;
		if (is_array($digits)){
			foreach ($digits as $k=>$v){
				$version += ($v * pow(10, max(0, (3-$k))));
			}
			
		}
		return $version;
	}
}