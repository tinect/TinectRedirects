(()=>{var $=Object.defineProperty;var t=(e,i)=>()=>(e&&(i=e(e=0)),i);var l=(e,i)=>{for(var s in i)$(e,s,{get:i[s],enumerable:!0})};var d,o=t(()=>{d=`{% block tinect_redirects_list %}
    <sw-page class="tinect-redirects-list">

        <template #smart-bar-header>
            {% block tinect_redirects_list_header %}
                <h2>
                    {{ $tc('tinect-redirects.general.title') }}
                    <span v-if="!isLoading" class="sw-page__smart-bar-amount">({{ total }})</span>
                </h2>
            {% endblock %}
        </template>

        <template #search-bar>
            {% block tinect_redirects_list_search %}
                <sw-search-bar
                        initial-search-type="tinect_redirects"
                        :initial-search="term"
                        :placeholder="$tc('tinect-redirects.list.placeholderSearchBar')"
                        @search="onSearch"
                />
            {% endblock %}
        </template>

        <template #smart-bar-actions>
            {% block tinect_redirects_list_smarbar %}
                <sw-button variant="primary" :routerLink="{name: 'tinect.redirects.create'}">
                    {{ $t('tinect-redirects.list.createButton') }}
                </sw-button>
                <sw-button variant="secondary" :routerLink="{ name: 'sw.extension.config', params: { namespace: 'TinectRedirects' } }">
                    {{ $t('tinect-redirects.list.openPluignConfig') }}
                </sw-button>
            {% endblock %}
        </template>

        <template #content>
            {% block tinect_redirects_list_content %}
                <sw-entity-listing
                        v-if="isLoading || items"
                        :items="items"
                        :repository="redirectRepository"
                        :columns="columns"
                        :sort-by="sortBy"
                        :sort-direction="sortDirection"
                        :isLoading="isLoading"
                        :disable-data-fetching="true"
                        :allow-edit="acl.can('tinect_redirects_redirect.editor')"
                        :allow-delete="acl.can('tinect_redirects_redirect.deleter')"
                        identifier="tinect-redirect-list"
                        detail-route="tinect.redirects.details"
                        @update-records="updateTotal"
                        @page-change="onPageChange"
                        @column-sort="onSortColumn"
                >
                    <template #column-active="{ item }">
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

                    <template #column-source="{ item }">
                        <router-link :to="{ name: 'tinect.redirects.details', params: { id: item.id } }">
                            <template v-if="item.salesChannelDomain">
                                {{ item.salesChannelDomain.url }}{{ item.source }}
                            </template>
                            <template v-else>
                                {{ item.source }}
                            </template>
                        </router-link>
                    </template>

                </sw-entity-listing>
            {% endblock %}
        </template>

        <template #sidebar>
            {% block tinect_redirects_list_sidebar %}
                <sw-sidebar>
                    <sw-sidebar-item
                            icon="regular-undo"
                            :title="$tc('tinect-redirects.sidebar.refresh')"
                            @click="onRefresh">
                    </sw-sidebar-item>

                    <sw-sidebar-item icon="regular-filter" :title="$tc('tinect-redirects.sidebar.filter.title')">

                        <mt-select
                            :label="$t('tinect-redirects.sidebar.filter.active.label')"
                            v-model="filter.active"
                            :options="filterActiveOptions"
                        />

                        <sw-number-field
                                v-model:value="filter.minimumRequestCount"
                                :label="$t('tinect-redirects.sidebar.filter.minimumRequests.label')"
                                type="number"
                                numberType="int"
                                :allowEmpty="true"
                                :min="0" :step="1"
                        ></sw-number-field>

                        <sw-entity-single-select
                                v-model:value="filter.salesChannelDomainId"
                                :label="$t('tinect-redirects.sidebar.filter.domain.label')"
                                entity="sales_channel_domain"
                                labelProperty="url"
                                show-clearable-button
                        ></sw-entity-single-select>

                        <sw-switch-field :label="$t('tinect-redirects.sidebar.filter.hidden.label')"
                                         v-model:value="filter.hidden"/>

                        <sw-button
                                variant="ghost"
                                @click="resetFilter">
                            {{ $tc('tinect-redirects.sidebar.filter.resetFilter') }}
                        </sw-button>
                    </sw-sidebar-item>
                </sw-sidebar>
            {% endblock %}
        </template>
    </sw-page>
{% endblock %}
`});var p={};l(p,{default:()=>q});var I,r,q,u=t(()=>{o();({Mixin:I}=Shopware),r=Shopware.Data.Criteria,q={template:d,inject:["repositoryFactory","acl"],mixins:[I.getByName("listing")],data(){return{items:null,isLoading:!0,total:0,sortBy:"createdAt",sortDirection:"DESC",filter:{salesChannelDomainId:null,active:0,hidden:0,minimumRequestCount:null}}},metaInfo(){return{title:this.$createTitle()}},computed:{redirectRepository(){return this.repositoryFactory.create("tinect_redirects_redirect")},columns(){return[{property:"active",dataIndex:"active",label:this.$tc("tinect-redirects.detail.activeLabel"),routerLink:"tinect.redirects.details",align:"center"},{property:"source",dataIndex:"source",label:this.$tc("tinect-redirects.detail.sourceUrlLabel"),routerLink:"tinect.redirects.details",allowResize:!0,primary:!0},{property:"target",dataIndex:"target",label:this.$tc("tinect-redirects.detail.targetUrlLabel"),routerLink:"tinect.redirects.details",allowResize:!0},{property:"httpCode",dataIndex:"httpCode",label:this.$tc("tinect-redirects.detail.httpCodeLabel"),routerLink:"tinect.redirects.details",allowResize:!0},{property:"count",dataIndex:"count",label:this.$tc("tinect-redirects.list.countColumn"),routerLink:"tinect.redirects.details",allowResize:!0}]},criteria(){let e=new r(this.page,this.limit);return e.setTerm(this.term),(this.filter.active===0||this.filter.active===1)&&e.addFilter(r.equals("active",this.filter.active)),e.addFilter(r.equals("hidden",this.filter.hidden)),e.addSorting(r.sort(this.sortBy,this.sortDirection,this.naturalSorting)),this.filter.salesChannelDomainId&&e.addFilter(r.equals("salesChannelDomainId",this.filter.salesChannelDomainId)),this.filter.minimumRequestCount&&e.addFilter(r.range("count",{gte:this.filter.minimumRequestCount})),e},filterActiveOptions(){return[{value:1,label:this.$tc("tinect-redirects.sidebar.filter.active.values.yes")},{value:0,label:this.$tc("tinect-redirects.sidebar.filter.active.values.no")}]}},watch:{filter:{handler(){this.getList()},deep:!0}},methods:{getList(){return this.isLoading=!0,this.redirectRepository.search(this.criteria,Shopware.Context.api).then(e=>{this.items=e,this.total=e.total,this.isLoading=!1})},resetFilter(){this.filter={salesChannelDomainId:null,active:0,hidden:0,minimumRequestCount:null}},updateTotal({total:e}){this.total=e}}}});var h,m=t(()=>{h=`{% block tinect_redirects_details %}
    <sw-page class="tinect-redirects-details">
        <template #smart-bar-actions>
            <sw-button
                    v-tooltip.bottom="tooltipCancel"
                    :disabled="redirect !== null && isLoading"
                    @click="onCancel"
            >
                {{ $tc('global.default.cancel') }}
            </sw-button>

            <sw-button-process
                    v-model:processSuccess="isSaveSuccessful"
                    v-tooltip.bottom="tooltipSave"
                    :is-loading="isLoading"
                    :disabled="isLoading"
                    variant="primary"
                    @click.prevent="onSave"
            >
                {{ $tc('global.default.save') }}
            </sw-button-process>
        </template>

        <template #content>
            <sw-card-view>
                <template v-if="isLoading">
                    <sw-skeleton variant="detail-bold"/>
                    <sw-skeleton/>
                </template>
                <template v-else>
                    <sw-card position-identifier="tinect-redirects-details">
                        <sw-container class="no-flex-grow-field" columns="1fr 1fr">
                            <sw-checkbox-field :label="$t('tinect-redirects.detail.activeLabel')"
                                               v-model:value="redirect.active">
                            </sw-checkbox-field>

                            <sw-checkbox-field :helpText="$t('tinect-redirects.detail.hiddenHelpText')"
                                               :label="$t('tinect-redirects.detail.hiddenLabel')"
                                               v-model:value="redirect.hidden">
                            </sw-checkbox-field>
                        </sw-container>

                        <sw-card :title="$t('tinect-redirects.detail.source')"
                                 position-identifier="tinect-redirects-details-source">
                            <sw-entity-single-select :label="$tc('tinect-redirects.detail.salesChannelDomain')"
                                                     :value="redirect.salesChannelDomainId"
                                                     v-model:value="redirect.salesChannelDomainId"
                                                     entity="sales_channel_domain"
                                                     show-clearable-button
                                                     labelProperty="url"
                                                     :helpText="$tc('tinect-redirects.detail.salesChannelDomainHelpText')">
                            </sw-entity-single-select>

                            <sw-text-field :label="$t('tinect-redirects.detail.sourceUrlLabel')"
                                      v-model:value="redirect.source"
                                      validation="required"></sw-text-field>
                        </sw-card>

                        <mt-select
                            :label="$t('tinect-redirects.detail.httpCodeLabel')"
                            v-model="redirect.httpCode"
                            :options="httpCodeOptions"
                        />

                        <sw-card v-if="redirect.httpCode !== 410" :title="$t('tinect-redirects.detail.target')"
                                 position-identifier="tinect-redirects-details-target">
                            <template v-if="hasSwUrlExt">
                                <sw-dynamic-url-ext-field :label="$t('tinect-redirects.detail.targetUrlLabel')"
                                                  v-model:value="redirect.target"
                                                  validation="required">
                                </sw-dynamic-url-ext-field>
                            </template>
                            <template v-else>
                                <sw-dynamic-url-field
                                        v-model:value="redirect.target"
                                        validation="required">
                                </sw-dynamic-url-field>
                            </template>
                        </sw-card>

                        <sw-text-editor :label="$t('tinect-redirects.detail.commentLabel')"
                                        v-model:value="redirect.comment"></sw-text-editor>
                    </sw-card>

                    <tinect-similar-redirects :redirect="redirect" v-if="!redirect._isNew" />
                    <tinect-similar-requests :redirect="redirect"  v-if="!redirect._isNew" />
                </template>
            </sw-card-view>
        </template>
    </sw-page>
{% endblock %}
`});var b=t(()=>{});var g={};l(g,{default:()=>P});var R,F,P,w=t(()=>{m();b();({Component:R,Mixin:F}=Shopware),P={template:h,inject:["repositoryFactory"],mixins:[F.getByName("notification")],shortcuts:{"SYSTEMKEY+S":"onSave",ESCAPE:"onCancel"},props:{redirectId:{type:String,default:null}},data(){return{redirect:null,isLoading:!1,isSaveSuccessful:!1}},metaInfo(){return{title:this.$createTitle()}},computed:{repository(){return this.repositoryFactory.create("tinect_redirects_redirect")},hasSwUrlExt(){return R.getComponentRegistry().has("sw-dynamic-url-ext-field")},tooltipSave(){return{message:`${this.$device.getSystemKey()} + S`,appearance:"light"}},tooltipCancel(){return{message:"ESC",appearance:"light"}},httpCodeOptions(){return[{value:301,label:this.$tc("tinect-redirects.detail.httpCodeLabelValues.301")},{value:302,label:this.$tc("tinect-redirects.detail.httpCodeLabelValues.302")},{value:410,label:this.$tc("tinect-redirects.detail.httpCodeLabelValues.410")}]}},watch:{redirectId(){this.getRedirect()}},created(){this.createdComponent()},methods:{createdComponent(){if(Shopware.ExtensionAPI.publishData({id:"tinect-redirects-detail__redirect",path:"redirect",scope:this}),this.redirectId){this.getRedirect();return}this.redirect=this.repository.create()},getRedirect(){this.isLoading=!0,this.repository.get(this.redirectId,Shopware.Context.api).then(e=>{this.redirect=e,this.isLoading=!1}).catch(()=>{this.createNotificationError({message:this.$tc("global.notification.notificationLoadingDataErrorMessage")}),this.isLoading=!1})},onSave(){this.isLoading=!0,this.repository.save(this.redirect).then(()=>{if(this.isLoading=!1,this.isSaveSuccessful=!0,this.redirectId===null){this.$router.push({name:"tinect.redirects.details",params:{id:this.redirect.id}});return}this.getRedirect()}).catch(e=>{throw this.isLoading=!1,this.createNotificationError({message:this.$tc("global.notification.notificationSaveErrorMessage",0,{entityName:this.download.id})}),e})},onCancel(){this.$router.push({name:"tinect.redirects.index"})}}}});var y,f=t(()=>{y=`<sw-card :isLoading="isLoading" v-if="redirect" position-identifier="tinect-redirects-requests">
    <template #title>
        {{ $tc('tinect-redirects.requests.title', total) }}
    </template>
    <template #grid>
        <sw-entity-listing
                v-if="total > 0"
                :fullPage="false"
                :repository="repository"
                :columns="columns"
                :items="items"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                :allow-edit="acl.can('tinect_redirects_redirect_request.editor')"
                :allow-delete="acl.can('tinect_redirects_redirect_request.deleter')"
                identifier="tinect-redirects-requests-list"
                :is-loading="isLoading"
                @page-change="onPageChange"
                @update-records="updateTotal"
        >
            <template #column-createdAt="{ item }">
                {{ date(item.createdAt) }}
            </template>

            <template #more-actions="{ item }">
                <template v-for="(checker, key) in getIPCheckers()">
                    <sw-context-menu-item
                        @click="openCheckIP(item.ipAddress, key)"
                    >
                        {{ $tc('tinect-redirects.requests.openCheckIP.' + key) }}
                    </sw-context-menu-item>
                </template>
            </template>
        </sw-entity-listing>

        <sw-empty-state
                v-else
                :absolute="false"
                icon="regular-rocket"
                :title="$tc('tinect-redirects.requests.empty')"
                subline=""
        />
    </template>
</sw-card>
`});var v={};l(v,{default:()=>B});var A,n,B,C=t(()=>{f();({Mixin:A}=Shopware),{Criteria:n}=Shopware.Data,B={template:y,inject:["repositoryFactory","acl"],mixins:[A.getByName("listing")],props:{redirect:{type:Object,default:null}},data(){return{items:null,isLoading:!0,sortBy:"createdAt",sortDirection:"DESC",page:1,limit:10}},computed:{columns(){return[{property:"ipAddress",label:"tinect-redirects.detail.columnIpAddress",allowResize:!0,sortable:!1},{property:"referer",label:"tinect-redirects.detail.columnReferer",allowResize:!0,sortable:!1},{property:"userAgent",label:"tinect-redirects.detail.columnUserAgent",allowResize:!0,sortable:!1},{property:"createdAt",label:"tinect-redirects.detail.columnCreatedAt",allowResize:!0,sortable:!1}]},repository(){return this.repositoryFactory.create("tinect_redirects_redirect_request")},criteria(){let e=new n(this.page,this.limit);return e.addFilter(n.equals("redirectId",this.redirect.id)),e.addSorting(n.sort(this.sortBy,this.sortDirection)),e},date(){return Shopware.Filter.getByName("date")}},methods:{getList(){return this.isLoading=!0,this.repository.search(this.criteria,Shopware.Context.api).then(e=>{this.total=e.total,this.items=e,this.isLoading=!1})},updateTotal({total:e}){this.total=e},getIPCheckers(){return{abuseipdb:{label:this.$tc("tinect-redirects.requests.ipCheckers.abuseipdb"),url(e){return"https://www.abuseipdb.com/check/"+e}},pulsedrive:{label:this.$tc("tinect-redirects.requests.ipCheckers.pulsedrive"),url(e){return"https://pulsedive.com/indicator/?ioc="+window.btoa(e)}},whois:{label:this.$tc("tinect-redirects.requests.ipCheckers.whois"),url(e){return"https://www.whois.com/whois/"+e}},shodan:{label:this.$tc("tinect-redirects.requests.ipCheckers.shodan"),url(e){return"https://www.shodan.io/host/"+e}}}},openCheckIP(e,i){let s=this.getIPCheckers()[i];s||console.error(`Unknown type: ${i}`),window.open(s.url(e),"_blank")}}}});var x,_=t(()=>{x=`<sw-card :isLoading="isLoading" v-if="redirect" position-identifier="tinect-redirects-similar">
    <template #title>
        {{ $tc('tinect-redirects.similar.title', total) }}
    </template>
    <template #grid>
        <sw-entity-listing
                v-if="total > 0"
                :fullPage="false"
                :repository="repository"
                :columns="columns"
                :items="items"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                :allow-edit="acl.can('tinect_redirects_redirect.editor')"
                :allow-delete="acl.can('tinect_redirects_redirect.deleter')"
                identifier="tinect-redirects-similar-list"
                :is-loading="isLoading"
                @page-change="onPageChange"
                @update-records="updateTotal"
        />
        <sw-empty-state
                v-else
                :absolute="false"
                icon="regular-rocket"
                :title="$tc('tinect-redirects.similar.empty')"
                subline=""
        />
    </template>
</sw-card>`});var L={};l(L,{default:()=>N});var z,a,N,k=t(()=>{_();({Mixin:z}=Shopware),{Criteria:a}=Shopware.Data,N={template:x,inject:["repositoryFactory","acl"],mixins:[z.getByName("listing")],props:{redirect:{type:Object,default:null}},data(){return{items:null,isLoading:!0,sortBy:"createdAt",sortDirection:"DESC",page:1,limit:10}},computed:{columns(){return[{property:"active",dataIndex:"active",label:this.$tc("tinect-redirects.detail.activeLabel"),routerLink:"tinect.redirects.details",align:"center",sortable:!1},{property:"source",dataIndex:"source",label:this.$tc("tinect-redirects.detail.sourceUrlLabel"),routerLink:"tinect.redirects.details",allowResize:!0,primary:!0,sortable:!1},{property:"target",dataIndex:"target",label:this.$tc("tinect-redirects.detail.targetUrlLabel"),allowResize:!0,sortable:!1},{property:"httpCode",dataIndex:"httpCode",label:this.$tc("tinect-redirects.detail.httpCodeLabel"),allowResize:!0,sortable:!1},{property:"count",dataIndex:"count",label:this.$tc("tinect-redirects.list.countColumn"),allowResize:!0,sortable:!1}]},repository(){return this.repositoryFactory.create("tinect_redirects_redirect")},criteria(){let e=new a(this.page,this.limit);return e.addFilter(a.equals("source",this.redirect.source)),e.addFilter(a.not("and",[a.equals("id",this.redirect.id)])),e.addSorting(a.sort(this.sortBy,this.sortDirection)),e}},methods:{getList(){return this.isLoading=!0,this.repository.search(this.criteria,Shopware.Context.api).then(e=>{this.total=e.total,this.items=e,this.isLoading=!1})},updateTotal({total:e}){this.total=e}}}});var{Component:c,Module:X}=Shopware;c.register("tinect-redirects-index",()=>Promise.resolve().then(()=>(u(),p)));c.register("tinect-redirects-details",()=>Promise.resolve().then(()=>(w(),g)));c.register("tinect-similar-requests",()=>Promise.resolve().then(()=>(C(),v)));c.register("tinect-similar-redirects",()=>Promise.resolve().then(()=>(k(),L)));Shopware.Module.register("tinect-redirects",{type:"plugin",name:"tinect-redirects",title:"tinect-redirects.general.title",description:"tinect-redirects.general.title",color:"#189eff",icon:"regular-rocket",entity:"tinect_redirects_redirect",routes:{index:{component:"tinect-redirects-index",path:"index"},create:{component:"tinect-redirects-details",path:"create",meta:{parentPath:"tinect.redirects.index"}},details:{component:"tinect-redirects-details",path:"details/:id",props:{default:e=>({redirectId:e.params.id})},meta:{parentPath:"tinect.redirects.index"}}},settingsItem:[{id:"tinect-redirects",path:"tinect.redirects.index",parent:"sw-settings",color:"#189eff",group:"plugins",to:"tinect.redirects.index",icon:"regular-double-chevron-right-s",label:"tinect-redirects.general.title"}],defaultSearchConfiguration:{_searchable:!0,source:{_searchable:!0,_score:500},target:{_searchable:!0,_score:500}}});Shopware.Component.override("sw-import-export-edit-profile-modal",{created(){this.supportedEntities.push({value:"tinect_redirects_redirect",label:this.$tc("sw-import-export.profile.rlRedirectLabel")})}});})();
