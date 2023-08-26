!function(e){var t={};function i(n){if(t[n])return t[n].exports;var r=t[n]={i:n,l:!1,exports:{}};return e[n].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=e,i.c=t,i.d=function(e,t,n){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)i.d(n,r,function(t){return e[t]}.bind(null,r));return n},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p=(window.__sw__.assetPath + '/bundles/tinectredirects/'),i(i.s="6cNB")}({"4YTf":function(e,t){Shopware.Component.override("sw-import-export-edit-profile-modal",{created:function(){this.supportedEntities.push({value:"tinect_redirects_redirect",label:this.$tc("sw-import-export.profile.rlRedirectLabel")}),console.log(this.supportedEntities)}})},"6cNB":function(e,t,i){"use strict";i.r(t);var n=Shopware,r=n.Component,s=n.Mixin,a=Shopware.Data.Criteria;r.register("tinect-redirects-index",{template:'{% block tinect_redirects_list %}\n\t<sw-page class="tinect-redirects-list">\n\t\t<template slot="smart-bar-actions">\n\t\t\t{% block tinect_redirects_list_smarbar %}\n\t\t\t\t<sw-button variant="primary" :routerLink="{name: \'tinect.redirects.create\'}">\n\t\t\t\t\t{{ $t(\'tinect-redirects.list.createButton\') }}\n\t\t\t\t</sw-button>\n\t\t\t{% endblock %}\n\t\t</template>\n\t\t<template slot="smart-bar-header">\n            <h2>{{ $tc(\'tinect-redirects.general.title\') }} ({{ total }})</h2>\n        </template>\n\t\t<template slot="content">\n\t\t\t{% block tinect_redirects_list_content %}\n                <tinect-redirects-list :items="items" :isLoading="isLoading">\n                </tinect-redirects-list>\n\t\t\t{% endblock %}\n\t\t</template>\n\n        <sw-sidebar slot="sidebar">\n            <sw-sidebar-item\n                icon="regular-undo"\n                :title="$tc(\'tinect-redirects.sidebar.refresh\')"\n                @click="onRefresh">\n            </sw-sidebar-item>\n\n            <sw-sidebar-item icon="regular-filter"\n                             :title="$tc(\'tinect-redirects.sidebar.filter\')">\n                <sw-text-field :label="$tc(\'tinect-redirects.sidebar.filters.search\')" v-model="filter.term"></sw-text-field>\n\n                <sw-select-field :label="$t(\'tinect-redirects.detail.activeLabel\')"\n                                        v-model="filter.active">\n                    <option value=""></option>\n                    <option value="1">true</option>\n                    <option value="0">false</option>\n                </sw-select-field>\n\n                <sw-select-number-field :label="$t(\'tinect-redirects.detail.hiddenLabel\')"\n                                        v-model="filter.hidden">\n                    <option value=1>true</option>\n                    <option value=0>false</option>\n                </sw-select-number-field>\n\n                <sw-entity-single-select\n                    v-model="filter.salesChannelDomainId"\n                    :label="$tc(\'tinect-redirects.detail.salesChannelDomain\')"\n                    entity="sales_channel_domain"\n                    labelProperty="url"\n                    show-clearable-button\n                ></sw-entity-single-select>\n\n                <sw-button\n                    variant="ghost"\n                    @click="resetFilter">\n                    {{ $tc(\'tinect-redirects.sidebar.filters.resetFilter\') }}\n                </sw-button>\n            </sw-sidebar-item>\n\n        </sw-sidebar>\n\t</sw-page>\n{% endblock %}\n',inject:["repositoryFactory"],mixins:[s.getByName("listing")],data:function(){return{items:null,isLoading:!0,total:0,filter:{term:null,salesChannelDomainId:null,active:null,hidden:0}}},metaInfo:function(){return{title:this.$createTitle()}},computed:{redirectRepository:function(){return this.repositoryFactory.create("tinect_redirects_redirect")}},methods:{getList:function(){var e=this;this.isLoading=!0;var t=new a;return this.filter.term&&t.setTerm(this.filter.term),this.filter.salesChannelDomainId&&t.addFilter(a.equals("salesChannelDomainId",this.filter.salesChannelDomainId)),this.filter.active&&t.addFilter(a.equals("active",this.filter.active)),t.addFilter(a.equals("hidden",this.filter.hidden)),t.addSorting(a.sort("active","ASC")),t.addSorting(a.sort("createdAt","DESC")),this.redirectRepository.search(t,Shopware.Context.api).then((function(t){e.items=t,e.total=t.total,e.isLoading=!1}))},resetFilter:function(){this.filter={term:null,salesChannelDomainId:null,active:null,hidden:0}}},watch:{filter:{deep:!0,handler:Shopware.Utils.debounce((function(){this.getList()}),400)}}});Shopware.Component.register("tinect-redirects-list",{template:'{% block tinect_redirects_list %}\n    <sw-entity-listing\n        v-if="items"\n        :items="items"\n        :repository="redirectRepository"\n        :columns="columns"\n        :isLoading="isLoading"\n        :fullPage="isFullPage"\n        detailRoute="tinect.redirect.details">\n        <template slot="column-source" slot-scope="{ item }">\n            <router-link\n                class="sw-data-grid__cell-value"\n                :to="{ name: \'tinect.redirects.details\', params: { id: item.id} }"\n            >\n                <template v-if="item.salesChannelDomain">\n                    {{ item.salesChannelDomain.url }}{{ item.source }}\n                </template>\n                <template v-else>\n                    {{ item.source }}\n                </template>\n            </router-link>\n        </template>\n\n        <template slot="detail-action" slot-scope="{ item }">\n            <sw-context-menu-item class="sw-entity-listing__context-menu-show-action" :routerLink="{ name: \'tinect.redirects.details\', params: { id: item.id } }">\n                {{ $tc(\'sw-customer.list.contextMenuEdit\') }}\n            </sw-context-menu-item>\n        </template>\n    </sw-entity-listing>\n{% endblock %}\n',props:{items:{type:Array,required:!1,default:null},isLoading:{type:Boolean,required:!1,default:!1},isFullPage:{type:Boolean,required:!1,default:!0}},inject:["repositoryFactory"],computed:{open:function(e){var t=this.$router.resolve({name:"tinect.redirects.details",params:{id:e}});window.location.replace(t.href)},columns:function(){return[{property:"active",dataIndex:"active",label:this.$tc("tinect-redirects.detail.activeLabel"),routerLink:"tinect.redirects.details"},{property:"source",dataIndex:"source",label:this.$tc("tinect-redirects.detail.sourceUrlLabel"),allowResize:!0,primary:!0},{property:"target",dataIndex:"target",label:this.$tc("tinect-redirects.detail.targetUrlLabel"),allowResize:!0},{property:"httpCode",dataIndex:"httpCode",label:this.$tc("tinect-redirects.detail.httpCodeLabel"),allowResize:!0},{property:"count",dataIndex:"count",label:this.$tc("tinect-redirects.detail.countLabel"),allowResize:!0}]},redirectRepository:function(){return this.repositoryFactory.create("tinect_redirects_redirect")}}});i("OhOC");var l=Shopware.Data.Criteria,o=Shopware,c=o.Component,d=o.Mixin;c.register("tinect-redirects-details",{template:'{% block tinect_redirects_details %}\n    <sw-page class="tinect-redirects-details">\n        <template slot="smart-bar-actions">\n            <sw-button :routerLink="{name: \'tinect.redirects.list\'}">\n                {{ $t(\'tinect-redirects.detail.cancelButton\') }}\n            </sw-button>\n\n            <sw-button-process :isLoading="isLoading"\n                               :processSuccess="processSuccess"\n                               variant="primary"\n                               @process-finish="saveFinish"\n                               @click="onClickSave">\n                {{ $t(\'tinect-redirects.detail.saveButton\') }}\n            </sw-button-process>\n        </template>\n\n        <template slot="content">\n            <sw-card-view>\n                <sw-card :isLoading="isLoading">\n                    <sw-container class="no-flex-grow-field">\n                        <sw-checkbox-field :label="$t(\'tinect-redirects.detail.activeLabel\')"\n                                           v-model="detail.active">\n                        </sw-checkbox-field>\n                    </sw-container>\n\n                    <sw-card>\n                        <sw-entity-single-select :label="$tc(\'tinect-redirects.detail.salesChannelDomain\')"\n                                                 :value="detail.salesChannelDomainId"\n                                                 v-model="detail.salesChannelDomainId"\n                                                 entity="sales_channel_domain"\n                                                 show-clearable-button\n                                                 labelProperty="url"\n                                                 :helpText="$tc(\'tinect-redirects.detail.salesChannelDomainHelpText\')">\n                        </sw-entity-single-select>\n\n                        <sw-field :label="$t(\'tinect-redirects.detail.sourceUrlLabel\')"\n                                  v-model="detail.source"\n                                  validation="required"></sw-field>\n\n                        <sw-field :label="$t(\'tinect-redirects.detail.countLabel\')"\n                                  v-model="detail.count"\n                                  disabled></sw-field>\n                    </sw-card>\n\n                    <template v-if="hasSwUrlExt">\n                        <sw-url-ext-field :label="$t(\'tinect-redirects.detail.targetUrlLabel\')"\n                                          v-model="detail.target"\n                                          validation="required"></sw-url-ext-field>\n                    </template>\n                    <template v-else>\n                        <sw-field :label="$t(\'tinect-redirects.detail.targetUrlLabel\')"\n                                  v-model="detail.target"\n                                  validation="required"></sw-field>\n                    </template>\n\n                    <sw-select-number-field :label="$t(\'tinect-redirects.detail.httpCodeLabel\')"\n                                            v-model="detail.httpCode"\n                                            validation="required">\n                        <option value=301>{{ $t(\'tinect-redirects.detail.httpCodeLabelValues.301\') }}</option>\n                        <option value=302>{{ $t(\'tinect-redirects.detail.httpCodeLabelValues.302\') }}</option>\n                    </sw-select-number-field>\n\n                    <sw-container class="no-flex-grow-field">\n                        <sw-checkbox-field :helpText="$t(\'tinect-redirects.detail.hiddenHelpText\')"\n                                           :label="$t(\'tinect-redirects.detail.hiddenLabel\')"\n                                           v-model="detail.hidden">\n                        </sw-checkbox-field>\n                    </sw-container>\n\n                    <sw-text-editor :label="$t(\'tinect-redirects.detail.commentLabel\')" v-model="detail.comment"></sw-text-editor>\n                </sw-card>\n\n                <sw-card :isLoading="similarItemsIsLoading" v-if="similarItems">\n                    <template slot="title">\n                        {{ similarItems.total }} similar Entries\n                    </template>\n                    <sw-container>\n                        <tinect-redirects-list :items="similarItems" :isLoading="similarItemsIsLoading" :isFullPage="!1">\n                        </tinect-redirects-list>\n                    </sw-container>\n                </sw-card>\n            </sw-card-view>\n\n        </template>\n    </sw-page>\n{% endblock %}\n',inject:["repositoryFactory"],mixins:[d.getByName("notification")],props:{redirectId:{type:String,required:!1,default:null}},data:function(){return{detail:{},isLoading:!0,processSuccess:!1,similarItems:null,similarItemsIsLoading:!0}},metaInfo:function(){return{title:this.$createTitle()}},computed:{redirectRepository:function(){return this.repositoryFactory.create("tinect_redirects_redirect")},hasSwUrlExt:function(){return c.getComponentRegistry().has("sw-url-ext-field")}},created:function(){this.getRedirect()},methods:{getRedirect:function(){var e=this;this.isLoading=!0,this.redirectRepository.get(this.redirectId,Shopware.Context.api).then((function(t){e.detail=t,e.isLoading=!1;var i=new l;i.addFilter(l.equals("source",t.source)),i.addFilter(l.not("and",[l.equals("id",t.id)])),e.redirectRepository.search(i,Shopware.Context.api).then((function(t){e.similarItems=t,e.similarItemsIsLoading=!1}))}))},onClickSave:function(){var e=this;this.detail.source!==this.detail.target?(this.isLoading=!0,this.redirectRepository.save(this.detail,Shopware.Context.api).then((function(){e.getRedirect(),e.isLoading=!1,e.processSuccess=!0})).catch((function(t){e.isLoading=!1,e.createNotificationError({title:e.$tc("tinect-redirects.detail.errorTitle"),message:t})}))):this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:this.$tc("tinect-redirects.detail.errorSameUrlDescription")})},saveFinish:function(){this.processSuccess=!1}},watch:{"$route.params.id":{handler:function(e){this.detail&&this.detail.id!==e&&this.$router.go()}}}});i("XyCq");Shopware.Module.register("tinect-redirects",{type:"plugin",name:"tinect-redirects",title:"tinect-redirects.general.title",description:"tinect-redirects.general.title",color:"#189eff",icon:"regular-rocket",routes:{list:{component:"tinect-redirects-index",path:"list"},details:{component:"tinect-redirects-details",path:"details/:id",props:{default:function(e){return{redirectId:e.params.id}}},meta:{parentPath:"tinect.redirects.list"}},create:{component:"tinect-redirects-create",path:"create",meta:{parentPath:"tinect.redirects.list"}}},settingsItem:[{id:"tinect-redirects",path:"tinect.redirects.list",parent:"sw-settings",group:"plugins",to:"tinect.redirects.list",icon:"regular-double-chevron-right-s",label:"tinect-redirects.general.title"}]});i("4YTf")},FjRI:function(e,t,i){},OhOC:function(e,t,i){var n=i("FjRI");n.__esModule&&(n=n.default),"string"==typeof n&&(n=[[e.i,n,""]]),n.locals&&(e.exports=n.locals);(0,i("P8hj").default)("7ecc2c37",n,!0,{})},P8hj:function(e,t,i){"use strict";function n(e,t){for(var i=[],n={},r=0;r<t.length;r++){var s=t[r],a=s[0],l={id:e+":"+r,css:s[1],media:s[2],sourceMap:s[3]};n[a]?n[a].parts.push(l):i.push(n[a]={id:a,parts:[l]})}return i}i.r(t),i.d(t,"default",(function(){return h}));var r="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!r)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var s={},a=r&&(document.head||document.getElementsByTagName("head")[0]),l=null,o=0,c=!1,d=function(){},u=null,p="data-vue-ssr-id",m="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function h(e,t,i,r){c=i,u=r||{};var a=n(e,t);return f(a),function(t){for(var i=[],r=0;r<a.length;r++){var l=a[r];(o=s[l.id]).refs--,i.push(o)}t?f(a=n(e,t)):a=[];for(r=0;r<i.length;r++){var o;if(0===(o=i[r]).refs){for(var c=0;c<o.parts.length;c++)o.parts[c]();delete s[o.id]}}}}function f(e){for(var t=0;t<e.length;t++){var i=e[t],n=s[i.id];if(n){n.refs++;for(var r=0;r<n.parts.length;r++)n.parts[r](i.parts[r]);for(;r<i.parts.length;r++)n.parts.push(b(i.parts[r]));n.parts.length>i.parts.length&&(n.parts.length=i.parts.length)}else{var a=[];for(r=0;r<i.parts.length;r++)a.push(b(i.parts[r]));s[i.id]={id:i.id,refs:1,parts:a}}}}function g(){var e=document.createElement("style");return e.type="text/css",a.appendChild(e),e}function b(e){var t,i,n=document.querySelector("style["+p+'~="'+e.id+'"]');if(n){if(c)return d;n.parentNode.removeChild(n)}if(m){var r=o++;n=l||(l=g()),t=y.bind(null,n,r,!1),i=y.bind(null,n,r,!0)}else n=g(),t=C.bind(null,n),i=function(){n.parentNode.removeChild(n)};return t(e),function(n){if(n){if(n.css===e.css&&n.media===e.media&&n.sourceMap===e.sourceMap)return;t(e=n)}else i()}}var v,w=(v=[],function(e,t){return v[e]=t,v.filter(Boolean).join("\n")});function y(e,t,i,n){var r=i?"":n.css;if(e.styleSheet)e.styleSheet.cssText=w(t,r);else{var s=document.createTextNode(r),a=e.childNodes;a[t]&&e.removeChild(a[t]),a.length?e.insertBefore(s,a[t]):e.appendChild(s)}}function C(e,t){var i=t.css,n=t.media,r=t.sourceMap;if(n&&e.setAttribute("media",n),u.ssrId&&e.setAttribute(p,t.id),r&&(i+="\n/*# sourceURL="+r.sources[0]+" */",i+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(r))))+" */"),e.styleSheet)e.styleSheet.cssText=i;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(i))}}},XyCq:function(e,t){Shopware.Component.extend("tinect-redirects-create","tinect-redirects-details",{methods:{getRedirect:function(){this.detail=this.redirectRepository.create(Shopware.Context.api),this.detail.httpCode=302,this.detail.active=!1,this.detail.hidden=!1,this.isLoading=!1},onClickSave:function(){var e=this;this.detail.source!==this.detail.target?(this.isLoading=!0,this.redirectRepository.save(this.detail,Shopware.Context.api).then((function(){e.isLoading=!1,e.$router.push({name:"tinect.redirects.details",params:{id:e.detail.id}})})).catch((function(t){e.isLoading=!1,e.createNotificationError({title:e.$tc("tinect-redirects.detail.errorTitle"),message:t})}))):this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:this.$tc("tinect-redirects.detail.errorSameUrlDescription")})}}})}});