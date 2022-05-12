!function(t){var e={};function i(r){if(e[r])return e[r].exports;var n=e[r]={i:r,l:!1,exports:{}};return t[r].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.m=t,i.c=e,i.d=function(t,e,r){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)i.d(r,n,function(e){return t[e]}.bind(null,n));return r},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="/bundles/tinectredirects/",i(i.s="LEs2")}({"2RFZ":function(t,e){t.exports='{% block tinect_redirects_list %}\n\t<sw-page class="tinect-redirects-list">\n\t\t<template slot="smart-bar-actions">\n\t\t\t{% block tinect_redirects_list_smarbar %}\n\t\t\t\t<sw-button variant="primary" :routerLink="{name: \'tinect.redirects.create\'}">\n\t\t\t\t\t{{ $t(\'tinect-redirects.list.createButton\') }}\n\t\t\t\t</sw-button>\n\t\t\t{% endblock %}\n\t\t</template>\n\t\t<template slot="content">\n\t\t\t{% block tinect_redirects_list_content %}\n\t\t\t\t<sw-entity-listing\n\t\t\t\t\tv-if="items"\n\t\t\t\t\t:items="items"\n\t\t\t\t\t:repository="redirectRepository"\n\t\t\t\t\t:columns="columns"\n                    :isLoading="isLoading"\n\t\t\t\t\tdetailRoute="tinect.redirect.details">\n\t\t\t\t</sw-entity-listing>\n\t\t\t{% endblock %}\n\t\t</template>\n\n        <sw-sidebar slot="sidebar">\n            <sw-sidebar-item\n                icon="default-arrow-360-left"\n                :title="$tc(\'tinect-redirects.sidebar.refresh\')"\n                @click="onRefresh">\n            </sw-sidebar-item>\n\n            <sw-sidebar-item icon="default-action-filter"\n                             :title="$tc(\'tinect-redirects.sidebar.filter\')">\n                <sw-text-field :label="$tc(\'tinect-redirects.sidebar.filters.search\')" v-model="filter.term"></sw-text-field>\n\n                <sw-entity-single-select\n                    v-model="filter.salesChannelDomainId"\n                    :label="$tc(\'tinect-redirects.detail.salesChannelDomain\')"\n                    entity="sales_channel_domain"\n                    labelProperty="url"\n                    show-clearable-button\n                ></sw-entity-single-select>\n\n                <sw-button\n                    variant="ghost"\n                    @click="resetFilter">\n                    {{ $tc(\'tinect-redirects.sidebar.filters.resetFilter\') }}\n                </sw-button>\n            </sw-sidebar-item>\n\n        </sw-sidebar>\n\t</sw-page>\n{% endblock %}\n'},LEs2:function(t,e,i){"use strict";i.r(e);var r=i("2RFZ"),n=i.n(r),s=Shopware,a=s.Component,c=s.Mixin,o=Shopware.Data.Criteria;a.register("tinect-redirects-list",{template:n.a,inject:["repositoryFactory"],mixins:[c.getByName("listing")],data:function(){return{items:null,isLoading:!0,filter:{term:null,salesChannelDomainId:null}}},metaInfo:function(){return{title:this.$createTitle()}},computed:{columns:function(){return[{property:"salesChannelDomain.url",dataIndex:"salesChannelDomain.url",label:this.$tc("tinect-redirects.detail.salesChannelDomain"),routerLink:"tinect.redirects.details",allowResize:!0,primary:!0},{property:"source",dataIndex:"source",label:this.$tc("tinect-redirects.detail.sourceUrlLabel"),routerLink:"tinect.redirects.details",allowResize:!0,primary:!0},{property:"target",dataIndex:"target",label:this.$tc("tinect-redirects.detail.targetUrlLabel"),allowResize:!0},{property:"httpCode",dataIndex:"httpCode",label:this.$tc("tinect-redirects.detail.httpCodeLabel"),allowResize:!0},{property:"active",dataIndex:"active",label:this.$tc("tinect-redirects.detail.activeLabel")}]},redirectRepository:function(){return this.repositoryFactory.create("tinect_redirects_redirect")}},methods:{getList:function(){var t=this;this.isLoading=!0;var e=new o;return e.addAssociation("salesChannelDomain"),this.filter.term&&e.setTerm(this.filter.term),this.filter.salesChannelDomainId&&e.addFilter(o.equals("salesChannelDomainId",this.filter.salesChannelDomainId)),e.addSorting(o.sort("active","ASC")),e.addSorting(o.sort("source","ASC")),this.redirectRepository.search(e,Shopware.Context.api).then((function(e){t.items=e,t.total=e.total,t.isLoading=!1}))},resetFilter:function(){this.filter={term:null,salesChannelDomainId:null}}},watch:{filter:{deep:!0,handler:Shopware.Utils.debounce((function(){this.getList()}),400)}}});var l=i("s+lq"),d=i.n(l),u=Shopware,p=u.Component,h=u.Mixin;p.register("tinect-redirects-details",{template:d.a,inject:["repositoryFactory"],mixins:[h.getByName("notification")],props:{redirectId:{type:String,required:!1,default:null}},data:function(){return{redirect:null,isLoading:!0,processSuccess:!1}},metaInfo:function(){return{title:this.$createTitle()}},computed:{redirectRepository:function(){return this.repositoryFactory.create("tinect_redirects_redirect")}},created:function(){this.getRedirect()},methods:{getRedirect:function(){var t=this;this.isLoading=!0,this.redirectRepository.get(this.redirectId,Shopware.Context.api).then((function(e){t.redirect=e,t.isLoading=!1}))},onClickSave:function(){var t=this;this.redirect.source!==this.redirect.target?(this.isLoading=!0,this.redirectRepository.save(this.redirect,Shopware.Context.api).then((function(){t.getRedirect(),t.isLoading=!1,t.processSuccess=!0})).catch((function(e){t.isLoading=!1,t.createNotificationError({title:t.$tc("tinect-redirects.detail.errorTitle"),message:e})}))):this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:this.$tc("tinect-redirects.detail.errorSameUrlDescription")})},saveFinish:function(){this.processSuccess=!1}}});i("Qazi");Shopware.Module.register("tinect-redirects",{type:"plugin",name:"tinect-redirects",title:"tinect-redirects.general.title",description:"tinect-redirects.general.title",color:"#189eff",icon:"default-object-rocket",routes:{list:{component:"tinect-redirects-list",path:"list"},details:{component:"tinect-redirects-details",path:"details/:id",props:{default:function(t){return{redirectId:t.params.id}}},meta:{parentPath:"tinect.redirects.list"}},create:{component:"tinect-redirects-create",path:"create",meta:{parentPath:"tinect.redirects.list"}}},settingsItem:[{id:"tinect-redirects",path:"tinect.redirects.list",parent:"sw-settings",group:"plugins",to:"tinect.redirects.list",icon:"small-arrow-large-double-right",label:"tinect-redirects.general.title"}]});i("NiRR")},NiRR:function(t,e){Shopware.Component.override("sw-import-export-edit-profile-modal",{created:function(){this.supportedEntities.push({value:"tinect_redirects_redirect",label:this.$tc("sw-import-export.profile.rlRedirectLabel")}),console.log(this.supportedEntities)}})},Qazi:function(t,e){Shopware.Component.extend("tinect-redirects-create","tinect-redirects-details",{methods:{getRedirect:function(){this.redirect=this.redirectRepository.create(Shopware.Context.api),this.redirect.httpCode=302,this.redirect.active=!1,this.isLoading=!1},onClickSave:function(){var t=this;this.redirect.source!==this.redirect.target?(this.isLoading=!0,this.redirectRepository.save(this.redirect,Shopware.Context.api).then((function(){t.isLoading=!1,t.$router.push({name:"tinect.redirects.details",params:{id:t.redirect.id}})})).catch((function(e){t.isLoading=!1,t.createNotificationError({title:t.$tc("tinect-redirects.detail.errorTitle"),message:e})}))):this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:this.$tc("tinect-redirects.detail.errorSameUrlDescription")})}}})},"s+lq":function(t,e){t.exports='{% block tinect_redirects_details %}\n    <sw-page class="tinect-redirects-details">\n        <template slot="smart-bar-actions">\n            <sw-button :routerLink="{name: \'tinect.redirects.list\'}">\n                {{ $t(\'tinect-redirects.detail.cancelButton\') }}\n            </sw-button>\n\n            <sw-button-process :isLoading="isLoading"\n                               :processSuccess="processSuccess"\n                               variant="primary"\n                               @process-finish="saveFinish"\n                               @click="onClickSave">\n                {{ $t(\'tinect-redirects.detail.saveButton\') }}\n            </sw-button-process>\n        </template>\n\n        <template slot="content">\n            <sw-card-view>\n                <sw-card :isLoading="isLoading">\n\n                    <sw-checkbox-field :label="$t(\'tinect-redirects.detail.activeLabel\')"\n                                       v-model="redirect.active"></sw-checkbox-field>\n\n                    <sw-field :label="$t(\'tinect-redirects.detail.sourceUrlLabel\')"\n                              v-model="redirect.source"\n                              validation="required"></sw-field>\n\n                    <sw-field :label="$t(\'tinect-redirects.detail.targetUrlLabel\')"\n                              v-model="redirect.target"\n                              validation="required"></sw-field>\n\n                    <sw-select-number-field :label="$t(\'tinect-redirects.detail.httpCodeLabel\')"\n                                            v-model="redirect.httpCode"\n                                            validation="required">\n                        <option value=301>{{ $t(\'tinect-redirects.detail.httpCodeLabelValues.301\') }}</option>\n                        <option value=302>{{ $t(\'tinect-redirects.detail.httpCodeLabelValues.302\') }}</option>\n                    </sw-select-number-field>\n\n                    <sw-entity-single-select :label="$tc(\'tinect-redirects.detail.salesChannelDomain\')"\n                                             :value="redirect.salesChannelDomainId"\n                                             v-model="redirect.salesChannelDomainId"\n                                             entity="sales_channel_domain"\n                                             show-clearable-button\n                                             labelProperty="url">\n                    </sw-entity-single-select>\n                </sw-card>\n            </sw-card-view>\n        </template>\n    </sw-page>\n{% endblock %}\n'}});