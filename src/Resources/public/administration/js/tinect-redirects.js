(()=>{var a=`{% block tinect_redirects_list %}
	<sw-page class="tinect-redirects-list">
		<template slot="smart-bar-actions">
			{% block tinect_redirects_list_smarbar %}
				<sw-button variant="primary" :routerLink="{name: 'tinect.redirects.create'}">
					{{ $t('tinect-redirects.list.createButton') }}
				</sw-button>
			{% endblock %}
		</template>
		<template slot="smart-bar-header">
            <h2>{{ $tc('tinect-redirects.general.title') }} ({{ total }})</h2>
        </template>
		<template slot="content">
			{% block tinect_redirects_list_content %}
                <sw-entity-listing
                        v-if="items"
                        :items="items"
                        :repository="redirectRepository"
                        :columns="columns"
                        :sort-by="currentSortBy"
                        :sort-direction="sortDirection"
                        :isLoading="isLoading"
                        :disable-data-fetching="true"
                        :fullPage="isFullPage"
                        detailRoute="tinect.redirect.details"
                        @page-change="onPageChange"
                        @column-sort="onSortColumn"
                >
                    <template slot="column-active" slot-scope="{ item }">
                        <sw-icon
                                v-if="item.active"
                                name="regular-checkmark-xs"
                                small
                                class="sw-icon--product-status is--active"
                        />

                        <sw-icon
                                v-else
                                name="regular-times-s"
                                small
                                class="sw-icon--product-status is--inactive"
                        />
                    </template>

                    <template slot="column-source" slot-scope="{ item }">
                        <router-link
                                class="sw-data-grid__cell-value"
                                :to="{ name: 'tinect.redirects.details', params: { id: item.id }, query: { limit, page } }"
                        >
                            <template v-if="item.salesChannelDomain">
                                {{ item.salesChannelDomain.url }}{{ item.source }}
                            </template>
                            <template v-else>
                                {{ item.source }}
                            </template>
                        </router-link>
                    </template>

                    <template slot="detail-action" slot-scope="{ item }">
                        <sw-context-menu-item class="sw-entity-listing__context-menu-show-action" :routerLink="{ name: 'tinect.redirects.details', params: { id: item.id }, query: { limit, page } }">
                            {{ $tc('sw-customer.list.contextMenuEdit') }}
                        </sw-context-menu-item>
                    </template>
                </sw-entity-listing>
            {% endblock %}
		</template>

        <template slot="sidebar">
            <sw-sidebar>
                <sw-sidebar-item
                    icon="regular-undo"
                    :title="$tc('tinect-redirects.sidebar.refresh')"
                    @click="onRefresh">
                </sw-sidebar-item>

                <sw-sidebar-item icon="regular-filter"
                                 :title="$tc('tinect-redirects.sidebar.filter')">
                    <sw-text-field :label="$tc('tinect-redirects.sidebar.filters.search')" v-model="filter.term"></sw-text-field>

                    <sw-select-field :label="$t('tinect-redirects.detail.activeLabel')"
                                            v-model="filter.active">
                        <option value=""></option>
                        <option value="1">true</option>
                        <option value="0">false</option>
                    </sw-select-field>

                    <sw-select-number-field :label="$t('tinect-redirects.detail.hiddenLabel')"
                                            v-model="filter.hidden">
                        <option value=1>true</option>
                        <option value=0>false</option>
                    </sw-select-number-field>

                    <sw-entity-single-select
                        v-model="filter.salesChannelDomainId"
                        :label="$tc('tinect-redirects.detail.salesChannelDomain')"
                        entity="sales_channel_domain"
                        labelProperty="url"
                        show-clearable-button
                    ></sw-entity-single-select>

                    <sw-button
                        variant="ghost"
                        @click="resetFilter">
                        {{ $tc('tinect-redirects.sidebar.filters.resetFilter') }}
                    </sw-button>
                </sw-sidebar-item>
            </sw-sidebar>
        </template>
	</sw-page>
{% endblock %}
`;var{Component:d,Mixin:m}=Shopware,i=Shopware.Data.Criteria;d.register("tinect-redirects-index",{template:a,inject:["repositoryFactory"],mixins:[m.getByName("listing")],data(){return{items:null,isLoading:!0,total:0,isFullPage:!0,filter:{term:null,salesChannelDomainId:null,active:null,hidden:0},firstEntryDate:null}},metaInfo(){return{title:this.$createTitle()}},async created(){let e=new i;e.addAggregation(i.min("createdAt","createdAt")),e.setLimit(1),await this.redirectRequestRepository.search(e,Shopware.Context.api).then(t=>{let s=new Date;this.firstEntryDate=Math.ceil((s-new Date(t.aggregations.createdAt.min))/(1e3*60*60*24))})},computed:{redirectRepository(){return this.repositoryFactory.create("tinect_redirects_redirect")},redirectRequestRepository(){return this.repositoryFactory.create("tinect_redirects_redirect_request")},columns(){return[{property:"active",dataIndex:"active",label:this.$tc("tinect-redirects.detail.activeLabel"),routerLink:"tinect.redirects.details",align:"center"},{property:"source",dataIndex:"source",label:this.$tc("tinect-redirects.detail.sourceUrlLabel"),allowResize:!0,primary:!0},{property:"target",dataIndex:"target",label:this.$tc("tinect-redirects.detail.targetUrlLabel"),allowResize:!0},{property:"httpCode",dataIndex:"httpCode",label:this.$tc("tinect-redirects.detail.httpCodeLabel"),allowResize:!0},{property:"count",dataIndex:"count",label:this.$tc("tinect-redirects.list.countColumn",this.firstEntryDate),allowResize:!0}]}},methods:{getList(){this.isLoading=!0;let e=new i(this.page,this.limit);return this.filter.term&&e.setTerm(this.filter.term),this.filter.salesChannelDomainId&&e.addFilter(i.equals("salesChannelDomainId",this.filter.salesChannelDomainId)),this.filter.active&&e.addFilter(i.equals("active",this.filter.active)),e.addFilter(i.equals("hidden",this.filter.hidden)),e.addSorting(i.sort("active","ASC")),e.addSorting(i.sort("createdAt","DESC")),this.redirectRepository.search(e,Shopware.Context.api).then(t=>{this.items=t,this.total=t.total,this.isLoading=!1})},resetFilter(){this.filter={term:null,salesChannelDomainId:null,active:null,hidden:0}}},watch:{filter:{deep:!0,handler:Shopware.Utils.debounce(function(){this.getList()},400)}}});var l=`{% block tinect_redirects_details %}
    <sw-page class="tinect-redirects-details">
        <template slot="smart-bar-actions">
            <sw-button :routerLink="{ name: 'tinect.redirects.list', query: { limit, page }}">
                {{ $t('tinect-redirects.detail.cancelButton') }}
            </sw-button>

            <sw-button-process :isLoading="isLoading"
                               :processSuccess="processSuccess"
                               variant="primary"
                               @process-finish="saveFinish"
                               @click="onClickSave">
                {{ $t('tinect-redirects.detail.saveButton') }}
            </sw-button-process>
        </template>

        <template slot="content">
            <sw-card-view>
                <sw-card :isLoading="isLoading">
                    <sw-container class="no-flex-grow-field">
                        <sw-checkbox-field :label="$t('tinect-redirects.detail.activeLabel')"
                                           v-model="detail.active">
                        </sw-checkbox-field>
                    </sw-container>

                    <sw-card>
                        <sw-entity-single-select :label="$tc('tinect-redirects.detail.salesChannelDomain')"
                                                 :value="detail.salesChannelDomainId"
                                                 v-model="detail.salesChannelDomainId"
                                                 entity="sales_channel_domain"
                                                 show-clearable-button
                                                 labelProperty="url"
                                                 :helpText="$tc('tinect-redirects.detail.salesChannelDomainHelpText')">
                        </sw-entity-single-select>

                        <sw-field :label="$t('tinect-redirects.detail.sourceUrlLabel')"
                                  v-model="detail.source"
                                  validation="required"></sw-field>
                    </sw-card>

                    <template v-if="hasSwUrlExt">
                        <sw-url-ext-field :label="$t('tinect-redirects.detail.targetUrlLabel')"
                                          v-model="detail.target"
                                          validation="required"
                                          :helpText="$tc('tinect-redirects.detail.salesChannelDomainHelpText')">
                        </sw-url-ext-field>
                    </template>
                    <template v-else>
                        <sw-field :label="$t('tinect-redirects.detail.targetUrlLabel')"
                                  v-model="detail.target"
                                  validation="required"
                                  :helpText="$tc('tinect-redirects.detail.targetUrlHelpText')">
                        </sw-field>
                    </template>

                    <sw-select-number-field :label="$t('tinect-redirects.detail.httpCodeLabel')"
                                            v-model="detail.httpCode"
                                            validation="required">
                        <option value=301>{{ $t('tinect-redirects.detail.httpCodeLabelValues.301') }}</option>
                        <option value=302>{{ $t('tinect-redirects.detail.httpCodeLabelValues.302') }}</option>
                    </sw-select-number-field>

                    <sw-container class="no-flex-grow-field">
                        <sw-checkbox-field :helpText="$t('tinect-redirects.detail.hiddenHelpText')"
                                           :label="$t('tinect-redirects.detail.hiddenLabel')"
                                           v-model="detail.hidden">
                        </sw-checkbox-field>
                    </sw-container>

                    <sw-text-editor :label="$t('tinect-redirects.detail.commentLabel')" v-model="detail.comment"></sw-text-editor>
                </sw-card>

                <sw-card :isLoading="similarItemsIsLoading" v-if="similarItems">
                    <template slot="title">
                        {{ $tc('tinect-redirects.detail.similarItemsLabel', similarItems.total) }}
                    </template>
                    <sw-container>
                        <tinect-redirects-list :items="similarItems" :isLoading="similarItemsIsLoading" :isFullPage="!1">
                        </tinect-redirects-list>
                    </sw-container>
                </sw-card>

                <sw-card v-if="detail">
                    <template slot="title">
                        {{ $tc('tinect-redirects.detail.countLabel', redirectRequestsTotal) }}
                    </template>
                    <template>
                        <sw-entity-listing
                            :fullPage="false"
                            :repository="redirectRequestRepository"
                            :columns="redirectRequestColumns"
                            :items="redirectRequests"
                            @page-change="onPageChange"
                            @column-sort="onSortColumn"
                            :criteria-limit="limit"
                            :sort-by="currentSortBy"
                            :sort-direction="redirectRequestsSortDirection"
                        >
                            <template slot="column-createdAt" slot-scope="{ item }">
                                {{ item.createdAt | date }}
                            </template>
                        </sw-entity-listing>
                    </template>
                </sw-card>
            </sw-card-view>

        </template>

        <template slot="smart-bar-back">
            <router-link
                    class="smart-bar__back-btn"
                    :to="{name: 'tinect.redirects.list', query: { limit, page }}"
            >
                <sw-icon
                        name="regular-chevron-left"
                        small
                />
                <sw-icon
                        name="regular-rocket"
                        small
                />
            </router-link>
        </template>
    </sw-page>
{% endblock %}
`;var r=Shopware.Data.Criteria,{Component:c,Mixin:n}=Shopware;c.register("tinect-redirects-details",{template:l,inject:["repositoryFactory"],mixins:[n.getByName("notification"),n.getByName("listing")],props:{redirectId:{type:String,required:!1,default:null}},data(){return{detail:{},isLoading:!0,processSuccess:!1,similarItems:null,similarItemsIsLoading:!0,redirectRequests:null,redirectRequestsSortBy:"createdAt",redirectRequestsSortDirection:"DESC",redirectRequestsTotal:0}},metaInfo(){return{title:this.$createTitle()}},computed:{redirectRepository(){return this.repositoryFactory.create("tinect_redirects_redirect")},redirectRequestRepository(){return this.repositoryFactory.create("tinect_redirects_redirect_request")},redirectRequestColumns(){return[{property:"ipAddress",label:"tinect-redirects.detail.columnIpAddress",allowResize:!0},{property:"userAgent",label:"tinect-redirects.detail.columnUserAgent",allowResize:!0},{property:"createdAt",label:"tinect-redirects.detail.columnCreatedAt",allowResize:!0}]},redirectRequestCriteria(){let e=new r(this.page,this.limit);return e.addFilter(r.equals("redirectId",this.redirectId)),e.setTerm(this.term),e.addSorting(r.sort(this.redirectRequestsSortBy,this.redirectRequestsSortDirection,this.naturalSorting)),e},hasSwUrlExt(){return c.getComponentRegistry().has("sw-url-ext-field")}},created(){this.getRedirect()},methods:{getRedirect(){this.isLoading=!0,this.redirectRepository.get(this.redirectId,Shopware.Context.api).then(e=>{this.detail=e,this.isLoading=!1;let t=new r;t.addFilter(r.equals("source",e.source)),t.addFilter(r.not("and",[r.equals("id",e.id)])),this.redirectRepository.search(t,Shopware.Context.api).then(s=>{this.similarItems=s,this.similarItemsIsLoading=!1})})},onClickSave(){if(this.detail.source===this.detail.target){this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:this.$tc("tinect-redirects.detail.errorSameUrlDescription")});return}this.isLoading=!0,this.redirectRepository.save(this.detail,Shopware.Context.api).then(()=>{this.getRedirect(),this.isLoading=!1,this.processSuccess=!0}).catch(e=>{this.isLoading=!1,this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:e})})},saveFinish(){this.processSuccess=!1},async getList(){let e=await this.addQueryScores(this.term,this.redirectRequestCriteria);return this.redirectRequestRepository.search(e).then(t=>{this.redirectRequests=t,this.redirectRequestsTotal=t.total})}},watch:{"$route.params.id":{handler:function(e){this.detail&&this.detail.id!==e&&this.$router.go()}}}});var{Component:u}=Shopware;u.extend("tinect-redirects-create","tinect-redirects-details",{methods:{getRedirect(){this.detail=this.redirectRepository.create(Shopware.Context.api),this.detail.httpCode=302,this.detail.active=!1,this.detail.hidden=!1,this.isLoading=!1},onClickSave(){if(this.detail.source===this.detail.target){this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:this.$tc("tinect-redirects.detail.errorSameUrlDescription")});return}this.isLoading=!0,this.redirectRepository.save(this.detail,Shopware.Context.api).then(()=>{this.isLoading=!1,this.$router.push({name:"tinect.redirects.details",params:{id:this.detail.id}})}).catch(e=>{this.isLoading=!1,this.createNotificationError({title:this.$tc("tinect-redirects.detail.errorTitle"),message:e})})}}});Shopware.Module.register("tinect-redirects",{type:"plugin",name:"tinect-redirects",title:"tinect-redirects.general.title",description:"tinect-redirects.general.title",color:"#189eff",icon:"regular-rocket",routes:{list:{component:"tinect-redirects-index",path:"list"},details:{component:"tinect-redirects-details",path:"details/:id",props:{default:e=>({redirectId:e.params.id})},meta:{parentPath:"tinect.redirects.list"}},create:{component:"tinect-redirects-create",path:"create",meta:{parentPath:"tinect.redirects.list"}}},settingsItem:[{id:"tinect-redirects",path:"tinect.redirects.list",parent:"sw-settings",group:"plugins",to:"tinect.redirects.list",icon:"regular-double-chevron-right-s",label:"tinect-redirects.general.title"}]});Shopware.Component.override("sw-import-export-edit-profile-modal",{created(){this.supportedEntities.push({value:"tinect_redirects_redirect",label:this.$tc("sw-import-export.profile.rlRedirectLabel")})}});})();
