(self["webpackChunksymbiose"] = self["webpackChunksymbiose"] || []).push([["src_app_in_bookings_booking_module_ts"],{

/***/ 6770:
/*!*******************************************************!*\
  !*** ./src/app/in/bookings/booking-routing.module.ts ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingRoutingModule": () => (/* binding */ BookingRoutingModule)
/* harmony export */ });
/* harmony import */ var _angular_router__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @angular/router */ 9895);
/* harmony import */ var _booking_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./booking.component */ 136);
/* harmony import */ var _edit_booking_edit_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit/booking.edit.component */ 522);
/* harmony import */ var _composition_booking_composition_component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./composition/booking.composition.component */ 7122);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @angular/core */ 7716);






const routes = [
    {
        path: '',
        component: _booking_component__WEBPACK_IMPORTED_MODULE_0__.BookingComponent
    },
    {
        path: 'edit/:id',
        component: _edit_booking_edit_component__WEBPACK_IMPORTED_MODULE_1__.BookingEditComponent
    },
    {
        path: 'edit',
        component: _edit_booking_edit_component__WEBPACK_IMPORTED_MODULE_1__.BookingEditComponent
    },
    {
        path: 'composition/:id',
        component: _composition_booking_composition_component__WEBPACK_IMPORTED_MODULE_2__.BookingCompositionComponent
    },
];
class BookingRoutingModule {
}
BookingRoutingModule.ɵfac = function BookingRoutingModule_Factory(t) { return new (t || BookingRoutingModule)(); };
BookingRoutingModule.ɵmod = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdefineNgModule"]({ type: BookingRoutingModule });
BookingRoutingModule.ɵinj = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdefineInjector"]({ imports: [[_angular_router__WEBPACK_IMPORTED_MODULE_4__.RouterModule.forChild(routes)], _angular_router__WEBPACK_IMPORTED_MODULE_4__.RouterModule] });
(function () { (typeof ngJitMode === "undefined" || ngJitMode) && _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵsetNgModuleScope"](BookingRoutingModule, { imports: [_angular_router__WEBPACK_IMPORTED_MODULE_4__.RouterModule], exports: [_angular_router__WEBPACK_IMPORTED_MODULE_4__.RouterModule] }); })();


/***/ }),

/***/ 136:
/*!**************************************************!*\
  !*** ./src/app/in/bookings/booking.component.ts ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingComponent": () => (/* binding */ BookingComponent)
/* harmony export */ });
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! sb-shared-lib */ 4725);


class BookingComponent {
    constructor(auth) {
        this.auth = auth;
    }
    ngOnInit() {
        console.log('BookingComponent init');
        // redirect to bookings list
    }
}
BookingComponent.ɵfac = function BookingComponent_Factory(t) { return new (t || BookingComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_1__.AuthService)); };
BookingComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: BookingComponent, selectors: [["booking"]], decls: 0, vars: 0, template: function BookingComponent_Template(rf, ctx) { }, encapsulation: 2 });


/***/ }),

/***/ 3558:
/*!***********************************************!*\
  !*** ./src/app/in/bookings/booking.module.ts ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "AppInBookingModule": () => (/* binding */ AppInBookingModule)
/* harmony export */ });
/* harmony import */ var _angular_material_core__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @angular/material/core */ 7817);
/* harmony import */ var _angular_cdk_platform__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! @angular/cdk/platform */ 521);
/* harmony import */ var _customDateAdapter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../customDateAdapter */ 1189);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _booking_routing_module__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./booking-routing.module */ 6770);
/* harmony import */ var _booking_component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./booking.component */ 136);
/* harmony import */ var _edit_booking_edit_component__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./edit/booking.edit.component */ 522);
/* harmony import */ var _edit_components_booking_edit_customer_booking_edit_customer_component__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./edit/components/booking.edit.customer/booking.edit.customer.component */ 6177);
/* harmony import */ var _edit_components_booking_edit_sojourn_booking_edit_sojourn_component__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./edit/components/booking.edit.sojourn/booking.edit.sojourn.component */ 1351);
/* harmony import */ var _edit_components_booking_edit_bookings_booking_edit_bookings_component__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./edit/components/booking.edit.bookings/booking.edit.bookings.component */ 6892);
/* harmony import */ var _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_booking_edit_bookings_group_component__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./edit/components/booking.edit.bookings/components/booking.edit.bookings.group/booking.edit.bookings.group.component */ 6340);
/* harmony import */ var _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_line_booking_edit_bookings_group_line_component__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./edit/components/booking.edit.bookings/components/booking.edit.bookings.group.line/booking.edit.bookings.group.line.component */ 6377);
/* harmony import */ var _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_accomodation_booking_edit_bookings_group_accomodation_component__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./edit/components/booking.edit.bookings/components/booking.edit.bookings.group.accomodation/booking.edit.bookings.group.accomodation.component */ 5795);
/* harmony import */ var _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_line_discount_booking_edit_bookings_group_line_discount_component__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./edit/components/booking.edit.bookings/components/booking.edit.bookings.group.line.discount/booking.edit.bookings.group.line.discount.component */ 6628);
/* harmony import */ var _composition_booking_composition_component__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./composition/booking.composition.component */ 7122);
/* harmony import */ var _composition_components_booking_composition_lines_booking_composition_lines_component__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./composition/components/booking.composition.lines/booking.composition.lines.component */ 3889);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @angular/core */ 7716);



















class AppInBookingModule {
}
AppInBookingModule.ɵfac = function AppInBookingModule_Factory(t) { return new (t || AppInBookingModule)(); };
AppInBookingModule.ɵmod = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_13__["ɵɵdefineNgModule"]({ type: AppInBookingModule });
AppInBookingModule.ɵinj = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_13__["ɵɵdefineInjector"]({ providers: [
        { provide: _angular_material_core__WEBPACK_IMPORTED_MODULE_14__.DateAdapter, useClass: _customDateAdapter__WEBPACK_IMPORTED_MODULE_0__.CustomDateAdapter, deps: [_angular_material_core__WEBPACK_IMPORTED_MODULE_14__.MAT_DATE_LOCALE, _angular_cdk_platform__WEBPACK_IMPORTED_MODULE_15__.Platform] }
    ], imports: [[
            sb_shared_lib__WEBPACK_IMPORTED_MODULE_16__.SharedLibModule,
            _booking_routing_module__WEBPACK_IMPORTED_MODULE_1__.BookingRoutingModule
        ]] });
(function () { (typeof ngJitMode === "undefined" || ngJitMode) && _angular_core__WEBPACK_IMPORTED_MODULE_13__["ɵɵsetNgModuleScope"](AppInBookingModule, { declarations: [_booking_component__WEBPACK_IMPORTED_MODULE_2__.BookingComponent, _edit_booking_edit_component__WEBPACK_IMPORTED_MODULE_3__.BookingEditComponent,
        _edit_components_booking_edit_customer_booking_edit_customer_component__WEBPACK_IMPORTED_MODULE_4__.BookingEditCustomerComponent, _edit_components_booking_edit_customer_booking_edit_customer_component__WEBPACK_IMPORTED_MODULE_4__.DialogCreatePartner,
        _edit_components_booking_edit_sojourn_booking_edit_sojourn_component__WEBPACK_IMPORTED_MODULE_5__.BookingEditSojournComponent, _edit_components_booking_edit_sojourn_booking_edit_sojourn_component__WEBPACK_IMPORTED_MODULE_5__.DialogCreateContact,
        _edit_components_booking_edit_bookings_booking_edit_bookings_component__WEBPACK_IMPORTED_MODULE_6__.BookingEditBookingsComponent,
        _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_booking_edit_bookings_group_component__WEBPACK_IMPORTED_MODULE_7__.BookingEditBookingsGroupComponent, _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_accomodation_booking_edit_bookings_group_accomodation_component__WEBPACK_IMPORTED_MODULE_9__.BookingEditBookingsGroupAccomodationComponent, _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_line_booking_edit_bookings_group_line_component__WEBPACK_IMPORTED_MODULE_8__.BookingEditBookingsGroupLineComponent, _edit_components_booking_edit_bookings_components_booking_edit_bookings_group_line_discount_booking_edit_bookings_group_line_discount_component__WEBPACK_IMPORTED_MODULE_10__.BookingEditBookingsGroupLineDiscountComponent,
        _composition_booking_composition_component__WEBPACK_IMPORTED_MODULE_11__.BookingCompositionComponent, _composition_booking_composition_component__WEBPACK_IMPORTED_MODULE_11__.BookingCompositionDialogConfirm,
        _composition_components_booking_composition_lines_booking_composition_lines_component__WEBPACK_IMPORTED_MODULE_12__.BookingCompositionLinesComponent], imports: [sb_shared_lib__WEBPACK_IMPORTED_MODULE_16__.SharedLibModule,
        _booking_routing_module__WEBPACK_IMPORTED_MODULE_1__.BookingRoutingModule] }); })();


/***/ }),

/***/ 7122:
/*!**************************************************************************!*\
  !*** ./src/app/in/bookings/composition/booking.composition.component.ts ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingCompositionComponent": () => (/* binding */ BookingCompositionComponent),
/* harmony export */   "BookingCompositionDialogConfirm": () => (/* binding */ BookingCompositionDialogConfirm)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var _angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @angular/material/dialog */ 2238);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_router__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @angular/router */ 9895);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_tabs__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @angular/material/tabs */ 5939);
/* harmony import */ var _angular_material_progress_spinner__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @angular/material/progress-spinner */ 4885);
/* harmony import */ var _components_booking_composition_lines_booking_composition_lines_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/booking.composition.lines/booking.composition.lines.component */ 3889);











function BookingCompositionComponent_mat_spinner_15_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](0, "mat-spinner");
} }
function BookingCompositionComponent_booking_composition_lines_16_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](0, "booking-composition-lines", 14);
} if (rf & 2) {
    const ctx_r1 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("composition_id", ctx_r1.booking.composition_id);
} }
function BookingCompositionComponent_ng_template_18_Template(rf, ctx) { if (rf & 1) {
    const _r4 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span", 15);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingCompositionComponent_ng_template_18_Template_span_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r4); const ctx_r3 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](); return ctx_r3.viewFullList(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1, "Listing complet");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
const _c0 = function (a0) { return { "hidden": a0 }; };
class Booking {
    constructor(id = 0, name = '', composition_id = 0) {
        this.id = id;
        this.name = name;
        this.composition_id = composition_id;
    }
}
class BookingCompositionComponent {
    constructor(dialog, api, route, context, zone) {
        this.dialog = dialog;
        this.api = api;
        this.route = route;
        this.context = context;
        this.zone = zone;
        this.showSbContainer = false;
        this.selectedTabIndex = 0;
        this.loading = true;
        this.booking = new Booking();
    }
    /**
     * Set up callbacks when component DOM is ready.
     */
    ngAfterContentInit() {
        this.loading = false;
        // _open and _close event are relayed by eqListener on the DOM node given as target when a context is requested
        // #sb-booking-container is defined in booking.edit.component.html
        $('#sb-composition-container').on('_close', (event, data) => {
            this.zone.run(() => {
                console.log('hiding container');
                this.showSbContainer = false;
                this.selectedTabIndex = 0;
            });
        });
        $('#sb-composition-container').on('_open', (event, data) => {
            this.zone.run(() => {
                console.log('showing container');
                this.showSbContainer = true;
            });
        });
    }
    ngOnInit() {
        // fetch the booking ID from the route
        this.route.params.subscribe((params) => (0,tslib__WEBPACK_IMPORTED_MODULE_2__.__awaiter)(this, void 0, void 0, function* () {
            if (params && params.hasOwnProperty('id')) {
                try {
                    this.booking_id = params['id'];
                    const booking = yield this.load(Object.getOwnPropertyNames(new Booking()));
                    this.booking = new Booking(booking.id, booking.name, booking.composition_id);
                }
                catch (error) {
                    console.warn(error);
                }
            }
        }));
    }
    load(fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_2__.__awaiter)(this, void 0, void 0, function* () {
            const result = yield this.api.read("lodging\\sale\\booking\\Booking", [this.booking_id], fields);
            if (result && result.length) {
                return result[0];
            }
            return {};
        });
    }
    /**
     * Request a new eQ context for selecting a payer, and relay change to self::payerChange(), if an object was created
     * #sb-booking-container is defined in booking.edit.component.html
     */
    viewFullList() {
        // 
        this.selectedTabIndex = 1;
        let descriptor = {
            context: {
                entity: 'sale\\booking\\CompositionItem',
                type: 'list',
                name: 'default',
                domain: ['composition_id', '=', this.booking.composition_id],
                mode: 'view',
                purpose: 'view',
                target: '#sb-composition-container',
                callback: (data) => {
                    if (data && data.objects && data.objects.length) {
                        // received data
                    }
                }
            }
        };
        // will trigger #sb-composition-container.on('_open')
        this.context.change(descriptor);
    }
    onGenerate() {
        const dialogRef = this.dialog.open(BookingCompositionDialogConfirm, {
            width: '50vw',
            data: { booking: this.booking }
        });
        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                console.log('answer is yes');
            }
            else {
                console.log('answer is no');
            }
        });
    }
}
BookingCompositionComponent.ɵfac = function BookingCompositionComponent_Factory(t) { return new (t || BookingCompositionComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__.MatDialog), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_4__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_router__WEBPACK_IMPORTED_MODULE_5__.ActivatedRoute), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_4__.ContextService), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_1__.NgZone)); };
BookingCompositionComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdefineComponent"]({ type: BookingCompositionComponent, selectors: [["booking-composition"]], decls: 20, vars: 7, consts: [[1, "container", 3, "ngClass"], [1, "booking-header"], [1, "booking-body"], [2, "display", "flex"], [2, "flex", "0 1 20%"], [2, "margin-left", "auto"], ["mat-stroked-button", "", "color", "primary", 3, "click"], [3, "selectedIndex"], ["label", "Listing par Unit\u00E9 locative"], [2, "margin-top", "20px"], [4, "ngIf"], [3, "composition_id", 4, "ngIf"], ["mat-tab-label", ""], ["id", "sb-composition-container", 1, "sb-container"], [3, "composition_id"], [3, "click"]], template: function BookingCompositionComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "Composition ");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](3, "span");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](4, " \u203A ");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](5);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](6, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](7, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](8, "div", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](9, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](10, "button", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingCompositionComponent_Template_button_click_10_listener() { return ctx.onGenerate(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](11, "G\u00E9n\u00E9rer la composition");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](12, "mat-tab-group", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](13, "mat-tab", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](14, "div", 9);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](15, BookingCompositionComponent_mat_spinner_15_Template, 1, 0, "mat-spinner", 10);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](16, BookingCompositionComponent_booking_composition_lines_16_Template, 1, 1, "booking-composition-lines", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](17, "mat-tab");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](18, BookingCompositionComponent_ng_template_18_Template, 2, 0, "ng-template", 12);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](19, "div", 13);
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngClass", _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpureFunction1"](5, _c0, ctx.showSbContainer));
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](5);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate1"](" R\u00E9servation ", ctx.booking.name, "");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](7);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("selectedIndex", ctx.selectedTabIndex);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](3);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.loading);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx.loading);
    } }, directives: [_angular_common__WEBPACK_IMPORTED_MODULE_6__.NgClass, _angular_material_button__WEBPACK_IMPORTED_MODULE_7__.MatButton, _angular_material_tabs__WEBPACK_IMPORTED_MODULE_8__.MatTabGroup, _angular_material_tabs__WEBPACK_IMPORTED_MODULE_8__.MatTab, _angular_common__WEBPACK_IMPORTED_MODULE_6__.NgIf, _angular_material_tabs__WEBPACK_IMPORTED_MODULE_8__.MatTabLabel, _angular_material_progress_spinner__WEBPACK_IMPORTED_MODULE_9__.MatSpinner, _components_booking_composition_lines_booking_composition_lines_component__WEBPACK_IMPORTED_MODULE_0__.BookingCompositionLinesComponent], styles: ["[_nghost-%COMP%] {\n  width: 100%;\n  height: 100%;\n  overflow: hidden;\n  box-sizing: border-box;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%] {\n  height: 100%;\n  width: 100%;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-header[_ngcontent-%COMP%] {\n  width: 100%;\n  padding-left: 12px;\n  height: 48px;\n  line-height: 48px;\n  border-bottom: solid 1px lightgrey;\n  font-size: 22px;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-body[_ngcontent-%COMP%] {\n  height: calc(100vh - 123px);\n  width: 100%;\n  overflow-y: scroll;\n  padding: 12px;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-body[_ngcontent-%COMP%]::-webkit-scrollbar {\n  width: 6px;\n  overflow-y: scroll;\n  background: transparent;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-body[_ngcontent-%COMP%]::-webkit-scrollbar-thumb {\n  background: var(--mdc-theme-primary, #6200ee);\n  border-radius: 10px;\n}\n[_nghost-%COMP%]   .container.hidden[_ngcontent-%COMP%] {\n  display: none;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuY29tcG9zaXRpb24uY29tcG9uZW50LnNjc3MiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7RUFFSSxXQUFBO0VBQ0EsWUFBQTtFQUNBLGdCQUFBO0VBQ0Esc0JBQUE7QUFBSjtBQUVJO0VBQ0ksWUFBQTtFQUNBLFdBQUE7QUFBUjtBQUVRO0VBQ0ksV0FBQTtFQUNBLGtCQUFBO0VBQ0EsWUFBQTtFQUNBLGlCQUFBO0VBQ0Esa0NBQUE7RUFDQSxlQUFBO0FBQVo7QUFHUTtFQUNJLDJCQUFBO0VBQ0EsV0FBQTtFQUNBLGtCQUFBO0VBQ0EsYUFBQTtBQURaO0FBSVE7RUFDSSxVQUFBO0VBQ0Esa0JBQUE7RUFDQSx1QkFBQTtBQUZaO0FBTVE7RUFDSSw2Q0FBQTtFQUNBLG1CQUFBO0FBSlo7QUFRSTtFQUNJLGFBQUE7QUFOUiIsImZpbGUiOiJib29raW5nLmNvbXBvc2l0aW9uLmNvbXBvbmVudC5zY3NzIiwic291cmNlc0NvbnRlbnQiOlsiOmhvc3Qge1xyXG5cclxuICAgIHdpZHRoOiAxMDAlO1xyXG4gICAgaGVpZ2h0OiAxMDAlO1xyXG4gICAgb3ZlcmZsb3c6IGhpZGRlbjtcclxuICAgIGJveC1zaXppbmc6IGJvcmRlci1ib3g7XHJcblxyXG4gICAgLmNvbnRhaW5lciB7XHJcbiAgICAgICAgaGVpZ2h0OiAxMDAlO1xyXG4gICAgICAgIHdpZHRoOiAxMDAlO1xyXG5cclxuICAgICAgICAuYm9va2luZy1oZWFkZXIge1xyXG4gICAgICAgICAgICB3aWR0aDogMTAwJTtcclxuICAgICAgICAgICAgcGFkZGluZy1sZWZ0OiAxMnB4O1xyXG4gICAgICAgICAgICBoZWlnaHQ6IDQ4cHg7XHJcbiAgICAgICAgICAgIGxpbmUtaGVpZ2h0OiA0OHB4O1xyXG4gICAgICAgICAgICBib3JkZXItYm90dG9tOiBzb2xpZCAxcHggbGlnaHRncmV5O1xyXG4gICAgICAgICAgICBmb250LXNpemU6IDIycHg7XHJcbiAgICAgICAgfVxyXG4gICAgXHJcbiAgICAgICAgLmJvb2tpbmctYm9keSB7XHJcbiAgICAgICAgICAgIGhlaWdodDogY2FsYygxMDB2aCAtIDEyM3B4KTtcclxuICAgICAgICAgICAgd2lkdGg6IDEwMCU7XHJcbiAgICAgICAgICAgIG92ZXJmbG93LXk6IHNjcm9sbDtcclxuICAgICAgICAgICAgcGFkZGluZzogMTJweDtcclxuICAgICAgICB9XHJcbiAgICBcclxuICAgICAgICAuYm9va2luZy1ib2R5Ojotd2Via2l0LXNjcm9sbGJhciB7XHJcbiAgICAgICAgICAgIHdpZHRoOiA2cHg7XHJcbiAgICAgICAgICAgIG92ZXJmbG93LXk6IHNjcm9sbDtcclxuICAgICAgICAgICAgYmFja2dyb3VuZDogdHJhbnNwYXJlbnQ7XHJcbiAgICAgICAgXHJcbiAgICAgICAgfVxyXG4gICAgICAgIFxyXG4gICAgICAgIC5ib29raW5nLWJvZHk6Oi13ZWJraXQtc2Nyb2xsYmFyLXRodW1iIHtcclxuICAgICAgICAgICAgYmFja2dyb3VuZDogdmFyKC0tbWRjLXRoZW1lLXByaW1hcnksICM2MjAwZWUpO1xyXG4gICAgICAgICAgICBib3JkZXItcmFkaXVzOiAxMHB4O1xyXG4gICAgICAgIH0gICAgICAgIFxyXG4gICAgfVxyXG5cclxuICAgIC5jb250YWluZXIuaGlkZGVuIHtcclxuICAgICAgICBkaXNwbGF5OiBub25lO1xyXG4gICAgfSAgXHJcbiAgICBcclxuICAgIFxyXG59Il19 */"] });
class BookingCompositionDialogConfirm {
    constructor(dialogRef, data) {
        this.dialogRef = dialogRef;
        this.data = data;
    }
}
BookingCompositionDialogConfirm.ɵfac = function BookingCompositionDialogConfirm_Factory(t) { return new (t || BookingCompositionDialogConfirm)(_angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__.MatDialogRef), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__.MAT_DIALOG_DATA)); };
BookingCompositionDialogConfirm.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdefineComponent"]({ type: BookingCompositionDialogConfirm, selectors: [["dialog-booking-composition-generate-confirm-dialog"]], decls: 18, vars: 3, consts: [["mat-dialog-title", ""], ["mat-dialog-content", ""], ["mat-dialog-actions", ""], ["mat-button", "", 3, "mat-dialog-close"], ["mat-button", "", "cdkFocusInitial", "", 3, "mat-dialog-close"]], template: function BookingCompositionDialogConfirm_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "h1", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1, "G\u00E9n\u00E9rer la composition");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](2, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](3, "p");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](4, "Cet assistant g\u00E9n\u00E9rera une composition sur base de la r\u00E9servation ");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](5, "b");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](6);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](7, ".");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](8, "p");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](9, "Les d\u00E9tails de la composition existante seront remplac\u00E9s et les \u00E9ventuels changements effectu\u00E9s seront perdus.");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](10, "p");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](11, "b");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](12, "Confirmez-vous la (re)g\u00E9n\u00E9ration ?");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](13, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](14, "button", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](15, "Annuler");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](16, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](17, "Cr\u00E9er");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](6);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate"](ctx.data.booking.name);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](8);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("mat-dialog-close", false);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("mat-dialog-close", true);
    } }, directives: [_angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__.MatDialogTitle, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__.MatDialogContent, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__.MatDialogActions, _angular_material_button__WEBPACK_IMPORTED_MODULE_7__.MatButton, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_3__.MatDialogClose], encapsulation: 2 });


/***/ }),

/***/ 3889:
/*!*********************************************************************************************************************!*\
  !*** ./src/app/in/bookings/composition/components/booking.composition.lines/booking.composition.lines.component.ts ***!
  \*********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingCompositionLinesComponent": () => (/* binding */ BookingCompositionLinesComponent)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @angular/cdk/drag-drop */ 7310);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @angular/material/snack-bar */ 7001);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_icon__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @angular/material/icon */ 6627);









function BookingCompositionLinesComponent_tr_10_button_15_Template(rf, ctx) { if (rf & 1) {
    const _r9 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "button", 9);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingCompositionLinesComponent_tr_10_button_15_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r9); const index_r3 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"]().index; _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](1); return (_r0.items[index_r3] = !_r0.items[index_r3]); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-icon", 18);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "keyboard_arrow_right");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingCompositionLinesComponent_tr_10_button_16_Template(rf, ctx) { if (rf & 1) {
    const _r12 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "button", 9);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingCompositionLinesComponent_tr_10_button_16_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r12); const index_r3 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"]().index; _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](1); return (_r0.items[index_r3] = !_r0.items[index_r3]); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-icon", 18);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "keyboard_arrow_up");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingCompositionLinesComponent_tr_10_div_18_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, "Voir le listing");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingCompositionLinesComponent_tr_10_div_19_div_11_div_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div", 27);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "div", 28);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r17 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](4);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate"](ctx_r17.selection.length > 0 ? ctx_r17.selection.length : 1);
} }
function BookingCompositionLinesComponent_tr_10_div_19_div_11_Template(rf, ctx) { if (rf & 1) {
    const _r19 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div", 23);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingCompositionLinesComponent_tr_10_div_19_div_11_Template_div_click_0_listener($event) { const restoredCtx = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r19); const item_r15 = restoredCtx.$implicit; const ctx_r18 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](3); return ctx_r18.onToggle($event, item_r15); })("cdkDragStarted", function BookingCompositionLinesComponent_tr_10_div_19_div_11_Template_div_cdkDragStarted_0_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r19); const ctx_r20 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](3); return ctx_r20.onDragStart($event); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](1, BookingCompositionLinesComponent_tr_10_div_19_div_11_div_1_Template, 3, 1, "div", 24);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "div", 25);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "div", 25);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](5);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](6, "div", 25);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](7);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](8, "div", 25);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](9);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](10, "div", 26);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](11, "button", 9);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingCompositionLinesComponent_tr_10_div_19_div_11_Template_button_click_11_listener() { const restoredCtx = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r19); const item_r15 = restoredCtx.$implicit; const ctx_r21 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](3); return ctx_r21.onOpenCompositionItem(item_r15.id); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](12, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](13, "open_in_new");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const item_r15 = ctx.$implicit;
    const i_r16 = ctx.index;
    const ctx_r14 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵclassProp"]("selected", item_r15.selected)("dragging", ctx_r14.dragging);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("cdkDragDisabled", !item_r15.selected);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"]("", i_r16 + 1, ".");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate"](item_r15.firstname);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate"](item_r15.lastname);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate"](item_r15.gender);
} }
function BookingCompositionLinesComponent_tr_10_div_19_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "div", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "div", 21);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](3, "#");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "div", 21);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](5, "Nom");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](6, "div", 21);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](7, "Pr\u00E9nom");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](8, "div", 21);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](9, "Genre");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](10, "div", 21);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](11, BookingCompositionLinesComponent_tr_10_div_19_div_11_Template, 14, 9, "div", 22);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const rental_unit_r2 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"]().$implicit;
    const ctx_r7 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](11);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngForOf", ctx_r7.composition_items[rental_unit_r2.id]);
} }
function BookingCompositionLinesComponent_tr_10_Template(rf, ctx) { if (rf & 1) {
    const _r24 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "tr");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "td");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "div", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](3, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](4);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](5, "div", 8);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](6, "button", 9);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingCompositionLinesComponent_tr_10_Template_button_click_6_listener() { const restoredCtx = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r24); const rental_unit_r2 = restoredCtx.$implicit; const ctx_r23 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r23.onOpenRentalUnit(rental_unit_r2.id); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](7, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](8, "open_in_new");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](9, "td", 10);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](10, "div", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](11);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](12, "td");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](13, "div", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](14, "div", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](15, BookingCompositionLinesComponent_tr_10_button_15_Template, 3, 0, "button", 14);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](16, BookingCompositionLinesComponent_tr_10_button_16_Template, 3, 0, "button", 14);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](17, "div", 15);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("cdkDropListDropped", function BookingCompositionLinesComponent_tr_10_Template_div_cdkDropListDropped_17_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r24); const ctx_r25 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r25.onDrop($event); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](18, BookingCompositionLinesComponent_tr_10_div_18_Template, 2, 0, "div", 16);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](19, BookingCompositionLinesComponent_tr_10_div_19_Template, 12, 1, "div", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const rental_unit_r2 = ctx.$implicit;
    const index_r3 = ctx.index;
    const ctx_r1 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"]();
    const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](4);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate3"]("", rental_unit_r2.name, ", ", rental_unit_r2.code, " (", rental_unit_r2.capacity, ")");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](7);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate"](ctx_r1.composition_items[rental_unit_r2.id].length);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](4);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", !_r0.items[index_r3]);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", _r0.items[index_r3]);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpropertyInterpolate"]("id", rental_unit_r2.id);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("cdkDropListData", rental_unit_r2.id);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", !_r0.items[index_r3]);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", _r0.items[index_r3]);
} }
const _c0 = function () { return []; };
const _c1 = function (a0) { return { items: a0 }; };
class Composition {
    constructor(id = 0, booking_id = 0) {
        this.id = id;
        this.booking_id = booking_id;
    }
}
class CompositionItem {
    constructor(id = 0, firstname = '', lastname = '', gender = '', date_of_birth = '', place_of_birth = '', email = '', phone = '', address = '', country = '', rental_unit_id = '') {
        this.id = id;
        this.firstname = firstname;
        this.lastname = lastname;
        this.gender = gender;
        this.date_of_birth = date_of_birth;
        this.place_of_birth = place_of_birth;
        this.email = email;
        this.phone = phone;
        this.address = address;
        this.country = country;
        this.rental_unit_id = rental_unit_id;
    }
}
class RentalUnit {
    constructor(id = 0, name = '', code = '', capacity = 0) {
        this.id = id;
        this.name = name;
        this.code = code;
        this.capacity = capacity;
    }
}
class BookingCompositionLinesComponent {
    constructor(api, cd, context, zone, snack) {
        this.api = api;
        this.cd = cd;
        this.context = context;
        this.zone = zone;
        this.snack = snack;
        this.composition = new Composition();
        // map of rental_unit_id mapping related composition items
        this.composition_items = {};
        this.rental_units = [];
        this.selection = [];
        this.dragging = false;
    }
    ngOnInit() {
    }
    ngOnChanges(changes) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_1__.__awaiter)(this, void 0, void 0, function* () {
            if (changes.composition_id) {
                try {
                    const compositions = yield this.api.read("sale\\booking\\Composition", [this.composition_id], Object.getOwnPropertyNames(new Composition()));
                    if (compositions.length) {
                        this.composition = compositions[0];
                        {
                            const data = yield this.load(Object.getOwnPropertyNames(new CompositionItem()));
                            for (let item of data) {
                                if (!this.composition_items.hasOwnProperty(item['rental_unit_id'])) {
                                    this.composition_items[item['rental_unit_id']] = [];
                                }
                                this.composition_items[item['rental_unit_id']].push(item);
                            }
                        }
                        console.log(this.composition_items);
                        {
                            const data = yield this.api.read("lodging\\realestate\\RentalUnit", Object.keys(this.composition_items), Object.getOwnPropertyNames(new RentalUnit()));
                            this.rental_units = data;
                        }
                        console.log(this.rental_units);
                    }
                }
                catch (error) {
                    console.warn(error);
                    this.snack.open('unknonw error');
                }
            }
        });
    }
    onToggle(event, item) {
        console.log('selecting', event, item);
        if (!item.hasOwnProperty('selected')) {
            item.selected = false;
        }
        let rental_unit_id = item.rental_unit_id;
        if (item.selected) {
            item.selected = false;
        }
        else {
            // unselect items from other containers
            for (let r_id of Object.keys(this.composition_items)) {
                if (r_id != rental_unit_id) {
                    for (let i in this.composition_items[r_id]) {
                        this.composition_items[r_id][i].selected = false;
                    }
                }
            }
            item.selected = true;
        }
        // update current selection
        this.selection = this.composition_items[rental_unit_id].filter((item) => item.selected);
        console.log(this.selection);
    }
    onDragStart(event) {
        this.dragging = true;
    }
    onDrop(event) {
        if (event.previousContainer != event.container) {
            console.log('from', event.previousContainer.data, 'to', event.container.data);
            let current_index = event.currentIndex;
            let target_rental_unit = event.container.data;
            let source_rental_unit = event.previousContainer.data;
            for (let item of this.selection) {
                let previous_index = this.composition_items[source_rental_unit].findIndex((a) => a.id == item.id);
                // move item
                (0,_angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_2__.transferArrayItem)(this.composition_items[source_rental_unit], this.composition_items[target_rental_unit], previous_index, current_index);
                ++current_index;
                // update item
                item.rental_unit_id = target_rental_unit;
                item.selected = false;
            }
            // reset selection
            this.selection = [];
        }
        this.dragging = false;
        console.log('dropped', event);
    }
    onOpenRentalUnit(rental_unit_id) {
        let descriptor = {
            context: {
                entity: 'lodging\\realestate\\RentalUnit',
                type: 'form',
                name: 'default',
                domain: ['id', '=', rental_unit_id],
                mode: 'view',
                purpose: 'view',
                target: '#sb-composition-container',
                callback: (data) => {
                    if (data && data.objects && data.objects.length) {
                        // received data
                    }
                }
            }
        };
        // will trigger #sb-composition-container.on('_open')
        this.context.change(descriptor);
    }
    onOpenCompositionItem(item_id) {
        let descriptor = {
            context: {
                entity: 'sale\\booking\\CompositionItem',
                type: 'form',
                name: 'default',
                domain: ['id', '=', item_id],
                mode: 'edit',
                purpose: 'view',
                target: '#sb-composition-container',
                callback: (data) => {
                    if (data && data.objects && data.objects.length) {
                        // received data
                    }
                }
            }
        };
        // will trigger #sb-composition-container.on('_open')
        this.context.change(descriptor);
    }
    load(fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_1__.__awaiter)(this, void 0, void 0, function* () {
            const result = yield this.api.collect("sale\\booking\\CompositionItem", [
                'composition_id', '=', this.composition.id
            ], fields, 'id', 'asc', 0, 500);
            return result;
        });
    }
}
BookingCompositionLinesComponent.ɵfac = function BookingCompositionLinesComponent_Factory(t) { return new (t || BookingCompositionLinesComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_3__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_0__.ChangeDetectorRef), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_3__.ContextService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_0__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_4__.MatSnackBar)); };
BookingCompositionLinesComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: BookingCompositionLinesComponent, selectors: [["booking-composition-lines"]], inputs: { composition_id: "composition_id" }, features: [_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵNgOnChangesFeature"]], decls: 11, vars: 5, consts: [[3, "var"], ["vUnit", "var"], [2, "width", "25%"], ["colspan", "2"], ["cdkDropListGroup", ""], [4, "ngFor", "ngForOf"], [2, "display", "flex"], [1, "cell", 2, "flex", "0 1 66%"], [1, "cell", 2, "margin-left", "auto"], ["mat-icon-button", "", 3, "click"], [2, "width", "8%", "text-align", "center"], [1, "cell"], [1, "part"], [1, "part-toggle"], ["mat-icon-button", "", 3, "click", 4, "ngIf"], ["cdkDropList", "", 1, "part-container", 3, "id", "cdkDropListData", "cdkDropListDropped"], [4, "ngIf"], ["class", "composition-container", 4, "ngIf"], [2, "font-size", "15px"], [1, "composition-container"], [1, "composition-container-header"], [1, "composition-container-column", "header"], ["cdkDrag", "", "class", "composition-container-item", 3, "selected", "dragging", "cdkDragDisabled", "click", "cdkDragStarted", 4, "ngFor", "ngForOf"], ["cdkDrag", "", 1, "composition-container-item", 3, "cdkDragDisabled", "click", "cdkDragStarted"], ["class", "drag-preview", 4, "cdkDragPreview"], [1, "composition-container-column"], [1, "composition-container-column", 2, "text-align", "right"], [1, "drag-preview"], [1, "drag-preview-inner"]], template: function BookingCompositionLinesComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](0, "div", 0, 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "table");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](3, "thead");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "tr");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](5, "th", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](6, "Unit\u00E9 locative");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](7, "th", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](8, "occupation");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](9, "tbody", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](10, BookingCompositionLinesComponent_tr_10_Template, 20, 10, "tr", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("var", _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpureFunction1"](3, _c1, _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpureFunction0"](2, _c0)));
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](10);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngForOf", ctx.rental_units);
    } }, directives: [sb_shared_lib__WEBPACK_IMPORTED_MODULE_3__.VarDirective, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_2__.CdkDropListGroup, _angular_common__WEBPACK_IMPORTED_MODULE_5__.NgForOf, _angular_material_button__WEBPACK_IMPORTED_MODULE_6__.MatButton, _angular_material_icon__WEBPACK_IMPORTED_MODULE_7__.MatIcon, _angular_common__WEBPACK_IMPORTED_MODULE_5__.NgIf, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_2__.CdkDropList, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_2__.CdkDrag, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_2__.CdkDragPreview], styles: ["[_nghost-%COMP%] {\n  width: 100%;\n  height: 100%;\n  overflow: hidden;\n  box-sizing: border-box;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%] {\n  border: solid 1px #ccc;\n  min-height: 60px;\n  background: white;\n  border-radius: 4px;\n  overflow: hidden;\n  display: block;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%]   .composition-container-header[_ngcontent-%COMP%] {\n  display: flex;\n  padding: 12px;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%]   .composition-container-column[_ngcontent-%COMP%] {\n  flex: 1;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%]   .composition-container-column.header[_ngcontent-%COMP%] {\n  font-weight: 500;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%]   .composition-container-item[_ngcontent-%COMP%] {\n  display: flex;\n  padding: 5px 10px;\n  border-bottom: solid 1px #ccc;\n  color: rgba(0, 0, 0, 0.87);\n  flex-direction: row;\n  align-items: center;\n  justify-content: space-between;\n  box-sizing: border-box;\n  cursor: move;\n  background: white;\n  font-size: 14px;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%]   .composition-container-item[_ngcontent-%COMP%]:last-child {\n  border: none;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%]   .composition-container-item.selected[_ngcontent-%COMP%] {\n  background-color: #f5f5ff;\n}\n[_nghost-%COMP%]   .composition-container[_ngcontent-%COMP%]   .composition-container-item.selected.dragging[_ngcontent-%COMP%] {\n  opacity: 0.5;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%] {\n  border-spacing: 0;\n  width: 100%;\n  border: solid 1px #e0e0e0;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%] {\n  min-width: 30px;\n  width: 30px;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   th[_ngcontent-%COMP%]:first-child, [_nghost-%COMP%]   table[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]:first-child {\n  padding-left: 2px;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   th[_ngcontent-%COMP%]:last-child, [_nghost-%COMP%]   table[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]:last-child {\n  padding-right: 2px;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   thead[_ngcontent-%COMP%] {\n  height: 45px;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   thead[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   th[_ngcontent-%COMP%], [_nghost-%COMP%]   table[_ngcontent-%COMP%]   thead[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%] {\n  text-align: center;\n  border-right: solid 1px #e0e0e0;\n  border-bottom: solid 1px #e0e0e0;\n  font-weight: 500;\n  color: black;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   thead[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   th[_ngcontent-%COMP%] {\n  position: sticky;\n  top: 0;\n  background: white;\n  z-index: 1;\n  box-shadow: 0 3px 5px 0 rgba(0, 0, 0, 0.1) !important;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   thead[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]:last-child {\n  border-right: 0;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   thead[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]:last-child {\n  box-shadow: 0 3px 5px 0 rgba(0, 0, 0, 0.1) !important;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   thead[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]:last-child   td[_ngcontent-%COMP%] {\n  border-bottom: 0;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tbody[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%] {\n  padding: 12px;\n  border-right: solid 1px #e0e0e0;\n  border-bottom: solid 1px #e0e0e0;\n  vertical-align: top;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tbody[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]   .cell[_ngcontent-%COMP%] {\n  min-height: 45px;\n  line-height: 45px;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tbody[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]   .part[_ngcontent-%COMP%] {\n  display: flex;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tbody[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]   .part[_ngcontent-%COMP%]   .part-toggle[_ngcontent-%COMP%] {\n  flex: 0 1;\n  display: inline-block;\n  vertical-align: top;\n  padding: 5px 0px 0px 20px;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tbody[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%] {\n  flex: 1;\n  display: inline-block;\n  width: 100%;\n  padding-top: 12px;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tbody[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]   td[_ngcontent-%COMP%]:last-child {\n  border-right: 0;\n}\n[_nghost-%COMP%]   table[_ngcontent-%COMP%]   tbody[_ngcontent-%COMP%]   tr[_ngcontent-%COMP%]:last-child   td[_ngcontent-%COMP%] {\n  border-bottom: 0;\n}\n.drag-preview[_ngcontent-%COMP%] {\n  padding-left: 20px;\n}\n.drag-preview[_ngcontent-%COMP%]   .drag-preview-inner[_ngcontent-%COMP%] {\n  background: #ff4081;\n  color: white;\n  font-weight: bold;\n  padding: 5px;\n  border-radius: 50%;\n  width: 30px;\n  height: 30px;\n  text-align: center;\n  display: inline-block;\n}\n.cdk-drop-list-dragging[_ngcontent-%COMP%]   .cdk-drag[_ngcontent-%COMP%] {\n  transition: transform 250ms cubic-bezier(0, 0, 0.2, 1);\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuY29tcG9zaXRpb24ubGluZXMuY29tcG9uZW50LnNjc3MiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7RUFFSSxXQUFBO0VBQ0EsWUFBQTtFQUNBLGdCQUFBO0VBQ0Esc0JBQUE7QUFBSjtBQUVJO0VBQ0ksc0JBQUE7RUFDQSxnQkFBQTtFQUNBLGlCQUFBO0VBQ0Esa0JBQUE7RUFDQSxnQkFBQTtFQUNBLGNBQUE7QUFBUjtBQUVRO0VBQ0ksYUFBQTtFQUNBLGFBQUE7QUFBWjtBQUlRO0VBQ0ksT0FBQTtBQUZaO0FBS1E7RUFDSSxnQkFBQTtBQUhaO0FBTVE7RUFDSSxhQUFBO0VBQ0EsaUJBQUE7RUFDQSw2QkFBQTtFQUNBLDBCQUFBO0VBRUEsbUJBQUE7RUFDQSxtQkFBQTtFQUNBLDhCQUFBO0VBQ0Esc0JBQUE7RUFDQSxZQUFBO0VBQ0EsaUJBQUE7RUFDQSxlQUFBO0FBTFo7QUFRUTtFQUNJLFlBQUE7QUFOWjtBQVNRO0VBQ0kseUJBQUE7QUFQWjtBQVVRO0VBQ0ksWUFBQTtBQVJaO0FBZUk7RUFDSSxpQkFBQTtFQUNBLFdBQUE7RUFDQSx5QkFBQTtBQWJSO0FBZ0JZO0VBQ0ksZUFBQTtFQUNBLFdBQUE7QUFkaEI7QUFrQlE7RUFDSSxpQkFBQTtBQWhCWjtBQW1CUTtFQUNJLGtCQUFBO0FBakJaO0FBcUJRO0VBQ0ksWUFBQTtBQW5CWjtBQXVCZ0I7RUFDSSxrQkFBQTtFQUNBLCtCQUFBO0VBQ0EsZ0NBQUE7RUFDQSxnQkFBQTtFQUNBLFlBQUE7QUFyQnBCO0FBd0JnQjtFQUNJLGdCQUFBO0VBQ0EsTUFBQTtFQUNBLGlCQUFBO0VBQ0EsVUFBQTtFQUNBLHFEQUFBO0FBdEJwQjtBQXlCZ0I7RUFDSSxlQUFBO0FBdkJwQjtBQTRCWTtFQUNJLHFEQUFBO0FBMUJoQjtBQTRCZ0I7RUFDSSxnQkFBQTtBQTFCcEI7QUFtQ2dCO0VBQ0ksYUFBQTtFQUNBLCtCQUFBO0VBQ0EsZ0NBQUE7RUFDQSxtQkFBQTtBQWpDcEI7QUFvQ29CO0VBQ0ksZ0JBQUE7RUFDQSxpQkFBQTtBQWxDeEI7QUFxQ29CO0VBQ0ksYUFBQTtBQW5DeEI7QUFxQ3dCO0VBQ0ksU0FBQTtFQUNBLHFCQUFBO0VBQ0EsbUJBQUE7RUFDQSx5QkFBQTtBQW5DNUI7QUFzQ3dCO0VBQ0ksT0FBQTtFQUNBLHFCQUFBO0VBQ0EsV0FBQTtFQUNBLGlCQUFBO0FBcEM1QjtBQTBDZ0I7RUFDSSxlQUFBO0FBeENwQjtBQTZDZ0I7RUFDSSxnQkFBQTtBQTNDcEI7QUFvREE7RUFDSSxrQkFBQTtBQWpESjtBQW1ESTtFQUNJLG1CQUFBO0VBQ0EsWUFBQTtFQUNBLGlCQUFBO0VBQ0EsWUFBQTtFQUNBLGtCQUFBO0VBQ0EsV0FBQTtFQUNBLFlBQUE7RUFDQSxrQkFBQTtFQUNBLHFCQUFBO0FBakRSO0FBcURBO0VBQ0ksc0RBQUE7QUFsREoiLCJmaWxlIjoiYm9va2luZy5jb21wb3NpdGlvbi5saW5lcy5jb21wb25lbnQuc2NzcyIsInNvdXJjZXNDb250ZW50IjpbIjpob3N0IHtcclxuXHJcbiAgICB3aWR0aDogMTAwJTtcclxuICAgIGhlaWdodDogMTAwJTtcclxuICAgIG92ZXJmbG93OiBoaWRkZW47XHJcbiAgICBib3gtc2l6aW5nOiBib3JkZXItYm94O1xyXG5cclxuICAgIC5jb21wb3NpdGlvbi1jb250YWluZXIge1xyXG4gICAgICAgIGJvcmRlcjogc29saWQgMXB4ICNjY2M7XHJcbiAgICAgICAgbWluLWhlaWdodDogNjBweDtcclxuICAgICAgICBiYWNrZ3JvdW5kOiB3aGl0ZTtcclxuICAgICAgICBib3JkZXItcmFkaXVzOiA0cHg7XHJcbiAgICAgICAgb3ZlcmZsb3c6IGhpZGRlbjtcclxuICAgICAgICBkaXNwbGF5OiBibG9jaztcclxuXHJcbiAgICAgICAgLmNvbXBvc2l0aW9uLWNvbnRhaW5lci1oZWFkZXIge1xyXG4gICAgICAgICAgICBkaXNwbGF5OiBmbGV4O1xyXG4gICAgICAgICAgICBwYWRkaW5nOiAxMnB4O1xyXG5cclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIC5jb21wb3NpdGlvbi1jb250YWluZXItY29sdW1uIHtcclxuICAgICAgICAgICAgZmxleDogMTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIC5jb21wb3NpdGlvbi1jb250YWluZXItY29sdW1uLmhlYWRlciB7XHJcbiAgICAgICAgICAgIGZvbnQtd2VpZ2h0OiA1MDA7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAuY29tcG9zaXRpb24tY29udGFpbmVyLWl0ZW0ge1xyXG4gICAgICAgICAgICBkaXNwbGF5OiBmbGV4O1xyXG4gICAgICAgICAgICBwYWRkaW5nOiA1cHggMTBweDtcclxuICAgICAgICAgICAgYm9yZGVyLWJvdHRvbTogc29saWQgMXB4ICNjY2M7XHJcbiAgICAgICAgICAgIGNvbG9yOiByZ2JhKDAsIDAsIDAsIDAuODcpO1xyXG4gICAgICAgICAgICBcclxuICAgICAgICAgICAgZmxleC1kaXJlY3Rpb246IHJvdztcclxuICAgICAgICAgICAgYWxpZ24taXRlbXM6IGNlbnRlcjtcclxuICAgICAgICAgICAganVzdGlmeS1jb250ZW50OiBzcGFjZS1iZXR3ZWVuO1xyXG4gICAgICAgICAgICBib3gtc2l6aW5nOiBib3JkZXItYm94O1xyXG4gICAgICAgICAgICBjdXJzb3I6IG1vdmU7XHJcbiAgICAgICAgICAgIGJhY2tncm91bmQ6IHdoaXRlO1xyXG4gICAgICAgICAgICBmb250LXNpemU6IDE0cHg7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIFxyXG4gICAgICAgIC5jb21wb3NpdGlvbi1jb250YWluZXItaXRlbTpsYXN0LWNoaWxkIHtcclxuICAgICAgICAgICAgYm9yZGVyOiBub25lO1xyXG4gICAgICAgIH1cclxuICAgIFxyXG4gICAgICAgIC5jb21wb3NpdGlvbi1jb250YWluZXItaXRlbS5zZWxlY3RlZCB7XHJcbiAgICAgICAgICAgIGJhY2tncm91bmQtY29sb3I6ICNmNWY1ZmY7XHJcbiAgICAgICAgfVxyXG4gICAgXHJcbiAgICAgICAgLmNvbXBvc2l0aW9uLWNvbnRhaW5lci1pdGVtLnNlbGVjdGVkLmRyYWdnaW5nIHtcclxuICAgICAgICAgICAgb3BhY2l0eTogMC41O1xyXG4gICAgICAgIH1cclxuICAgIH1cclxuXHJcblxyXG4gICAgXHJcblxyXG4gICAgdGFibGUge1xyXG4gICAgICAgIGJvcmRlci1zcGFjaW5nOiAwO1xyXG4gICAgICAgIHdpZHRoOiAxMDAlO1xyXG4gICAgICAgIGJvcmRlcjogc29saWQgMXB4ICNlMGUwZTA7XHJcblxyXG4gICAgICAgIHRyIHtcclxuICAgICAgICAgICAgdGQge1xyXG4gICAgICAgICAgICAgICAgbWluLXdpZHRoOiAzMHB4O1xyXG4gICAgICAgICAgICAgICAgd2lkdGg6IDMwcHg7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHRoOmZpcnN0LWNoaWxkLCB0ZDpmaXJzdC1jaGlsZCB7XHJcbiAgICAgICAgICAgIHBhZGRpbmctbGVmdDogMnB4O1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgdGg6bGFzdC1jaGlsZCwgdGQ6bGFzdC1jaGlsZCB7XHJcbiAgICAgICAgICAgIHBhZGRpbmctcmlnaHQ6IDJweDtcclxuICAgICAgICB9XHJcblxyXG5cclxuICAgICAgICB0aGVhZCB7XHJcbiAgICAgICAgICAgIGhlaWdodDogNDVweDtcclxuICAgICAgICAgICAgXHJcbiAgICAgICAgICAgIHRyIHtcclxuXHJcbiAgICAgICAgICAgICAgICB0aCwgdGQge1xyXG4gICAgICAgICAgICAgICAgICAgIHRleHQtYWxpZ246IGNlbnRlcjtcclxuICAgICAgICAgICAgICAgICAgICBib3JkZXItcmlnaHQ6IHNvbGlkIDFweCAjZTBlMGUwO1xyXG4gICAgICAgICAgICAgICAgICAgIGJvcmRlci1ib3R0b206IHNvbGlkIDFweCAjZTBlMGUwO1xyXG4gICAgICAgICAgICAgICAgICAgIGZvbnQtd2VpZ2h0OiA1MDA7XHJcbiAgICAgICAgICAgICAgICAgICAgY29sb3I6YmxhY2s7XHJcbiAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgdGgge1xyXG4gICAgICAgICAgICAgICAgICAgIHBvc2l0aW9uOiBzdGlja3k7XHJcbiAgICAgICAgICAgICAgICAgICAgdG9wOiAwO1xyXG4gICAgICAgICAgICAgICAgICAgIGJhY2tncm91bmQ6IHdoaXRlO1xyXG4gICAgICAgICAgICAgICAgICAgIHotaW5kZXg6IDE7XHJcbiAgICAgICAgICAgICAgICAgICAgYm94LXNoYWRvdzogMCAzcHggNXB4IDAgcmdiKDAgMCAwIC8gMTAlKSAhaW1wb3J0YW50O1xyXG4gICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgIHRkOmxhc3QtY2hpbGQge1xyXG4gICAgICAgICAgICAgICAgICAgIGJvcmRlci1yaWdodDogMDtcclxuICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIHRyOmxhc3QtY2hpbGQge1xyXG4gICAgICAgICAgICAgICAgYm94LXNoYWRvdzogMCAzcHggNXB4IDAgcmdiKDAgMCAwIC8gMTAlKSAhaW1wb3J0YW50O1xyXG5cclxuICAgICAgICAgICAgICAgIHRkIHtcclxuICAgICAgICAgICAgICAgICAgICBib3JkZXItYm90dG9tOiAwO1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgdGJvZHkge1xyXG4gICAgICAgICAgICB0ciB7XHJcblxyXG4gICAgICAgICAgICAgICAgdGQge1xyXG4gICAgICAgICAgICAgICAgICAgIHBhZGRpbmc6IDEycHg7XHJcbiAgICAgICAgICAgICAgICAgICAgYm9yZGVyLXJpZ2h0OiBzb2xpZCAxcHggI2UwZTBlMDtcclxuICAgICAgICAgICAgICAgICAgICBib3JkZXItYm90dG9tOiBzb2xpZCAxcHggI2UwZTBlMDtcclxuICAgICAgICAgICAgICAgICAgICB2ZXJ0aWNhbC1hbGlnbjogdG9wO1xyXG4gICAgICAgICAgICAgICAgICAgIFxyXG4gICAgICAgICAgICAgICAgXHJcbiAgICAgICAgICAgICAgICAgICAgLmNlbGwge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBtaW4taGVpZ2h0OiA0NXB4O1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsaW5lLWhlaWdodDogNDVweDtcclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIC5wYXJ0IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgZGlzcGxheTogZmxleDtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC5wYXJ0LXRvZ2dsZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBmbGV4OiAwIDE7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB2ZXJ0aWNhbC1hbGlnbjogdG9wO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcGFkZGluZzogNXB4IDBweCAwcHggMjBweDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgLnBhcnQtY29udGFpbmVyIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGZsZXg6IDE7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB3aWR0aDogMTAwJTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBhZGRpbmctdG9wOiAxMnB4O1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICB0ZDpsYXN0LWNoaWxkIHtcclxuICAgICAgICAgICAgICAgICAgICBib3JkZXItcmlnaHQ6IDA7XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIHRyOmxhc3QtY2hpbGQge1xyXG4gICAgICAgICAgICAgICAgdGQge1xyXG4gICAgICAgICAgICAgICAgICAgIGJvcmRlci1ib3R0b206IDA7XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcblxyXG4gICAgfVxyXG5cclxuXHJcbn1cclxuLmRyYWctcHJldmlldyB7XHJcbiAgICBwYWRkaW5nLWxlZnQ6IDIwcHg7XHJcblxyXG4gICAgLmRyYWctcHJldmlldy1pbm5lciB7XHJcbiAgICAgICAgYmFja2dyb3VuZDogI2ZmNDA4MTtcclxuICAgICAgICBjb2xvcjogd2hpdGU7XHJcbiAgICAgICAgZm9udC13ZWlnaHQ6IGJvbGQ7XHJcbiAgICAgICAgcGFkZGluZzogNXB4O1xyXG4gICAgICAgIGJvcmRlci1yYWRpdXM6IDUwJTtcclxuICAgICAgICB3aWR0aDogMzBweDtcclxuICAgICAgICBoZWlnaHQ6IDMwcHg7XHJcbiAgICAgICAgdGV4dC1hbGlnbjogY2VudGVyO1xyXG4gICAgICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcclxuICAgIH1cclxufVxyXG5cclxuLmNkay1kcm9wLWxpc3QtZHJhZ2dpbmcgLmNkay1kcmFnIHtcclxuICAgIHRyYW5zaXRpb246IHRyYW5zZm9ybSAyNTBtcyBjdWJpYy1iZXppZXIoMCwgMCwgMC4yLCAxKTtcclxufVxyXG4iXX0= */"] });


/***/ }),

/***/ 522:
/*!************************************************************!*\
  !*** ./src/app/in/bookings/edit/booking.edit.component.ts ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditComponent": () => (/* binding */ BookingEditComponent)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! rxjs */ 8229);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_router__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @angular/router */ 9895);
/* harmony import */ var _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @angular/material/dialog */ 2238);
/* harmony import */ var _angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @angular/material/snack-bar */ 7001);
/* harmony import */ var _angular_material_stepper__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @angular/material/stepper */ 4553);
/* harmony import */ var _components_booking_edit_customer_booking_edit_customer_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/booking.edit.customer/booking.edit.customer.component */ 6177);
/* harmony import */ var _components_booking_edit_sojourn_booking_edit_sojourn_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/booking.edit.sojourn/booking.edit.sojourn.component */ 1351);
/* harmony import */ var _components_booking_edit_bookings_booking_edit_bookings_component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/booking.edit.bookings/booking.edit.bookings.component */ 6892);











function BookingEditComponent_ng_template_7_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵtext"](0, "Client");
} }
function BookingEditComponent_ng_template_11_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵtext"](0, "S\u00E9jour");
} }
function BookingEditComponent_ng_template_15_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵtext"](0, "R\u00E9servations");
} }
/*
This is a SmartComponent.

Sub-components are in charge of:
 * loading sub-objects required for displaying the values related to the current booking.
 * creating sub-objects when required
 * send data update notifications to this component

Smart components are in charge of updating the model.

*/
class Booking {
    constructor(id = 0, name = '', created = new Date(), price = 0, customer_id = 0, has_payer_organisation = false, payer_organisation_id = 0, center_id = 0, type_id = 0, description = '', contacts_ids = [], booking_lines_groups_ids = []) {
        this.id = id;
        this.name = name;
        this.created = created;
        this.price = price;
        this.customer_id = customer_id;
        this.has_payer_organisation = has_payer_organisation;
        this.payer_organisation_id = payer_organisation_id;
        this.center_id = center_id;
        this.type_id = type_id;
        this.description = description;
        this.contacts_ids = contacts_ids;
        this.booking_lines_groups_ids = booking_lines_groups_ids;
    }
}
class BookingEditComponent {
    constructor(auth, api, router, dialog, route, snack, zone) {
        this.auth = auth;
        this.api = api;
        this.router = router;
        this.dialog = dialog;
        this.route = route;
        this.snack = snack;
        this.zone = zone;
        this._bookingInput = new rxjs__WEBPACK_IMPORTED_MODULE_4__.ReplaySubject(1);
        this._bookingOutput = new rxjs__WEBPACK_IMPORTED_MODULE_4__.ReplaySubject(1);
        this.showSbContainer = false;
    }
    /**
     * Set up callbacks when component DOM is ready.
     */
    ngAfterViewInit() {
        // _open and _close event are relayed by eqListener on the DOM node given as target when a context is requested
        // #sb-booking-container is defined in booking.edit.component.html
        $('#sb-booking-container').on('_close', (event, data) => {
            this.zone.run(() => {
                this.showSbContainer = false;
            });
        });
        $('#sb-booking-container').on('_open', (event, data) => {
            this.zone.run(() => {
                this.showSbContainer = true;
            });
        });
    }
    ngOnInit() {
        // listen to changes relayed by children component on the _bookingInput observable
        this._bookingInput.subscribe(params => this.update(params));
        // fetch the booking ID from the route
        this.route.params.subscribe((params) => (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            if (params && params.hasOwnProperty('id')) {
                this.id = params['id'];
                try {
                    // load booking object
                    let data = yield this.load(Object.getOwnPropertyNames(new Booking()));
                    this.booking = new Booking(data.id, data.name, new Date(data.created), data.price, data.customer_id, data.has_payer_organisation, data.payer_organisation_id, data.center_id, data.type_id, data.description, data.contacts_ids, data.booking_lines_groups_ids);
                    // relay to children
                    this._bookingOutput.next(this.booking);
                }
                catch (response) {
                    console.warn(response);
                }
            }
        }));
    }
    /**
     * Assign values based on selected booking and load sub-objects required by the view.
     *
     */
    load(fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            const result = yield this.api.read("lodging\\sale\\booking\\Booking", [this.id], fields);
            if (result && result.length) {
                return result[0];
            }
            return {};
        });
    }
    /**
     * Handler for updates relayed from children components
     */
    update(booking) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditComponent: received change', booking, this.booking);
            try {
                // handle requests for updating single fields
                if (booking.hasOwnProperty('refresh')) {
                    // some changes have been done that might impact current object
                    // refresh property specifies which fields have to be re-loaded
                    let model_fields = Object.getOwnPropertyNames(new Booking());
                    if (Array.isArray(booking.refresh)) {
                        model_fields = booking.refresh;
                    }
                    let data = yield this.load(model_fields);
                    this.booking = new Booking(data.id, data.name, new Date(data.created), data.price, data.customer_id, data.has_payer_organisation, data.payer_organisation_id, data.center_id, data.type_id, data.description, data.contacts_ids, data.booking_lines_groups_ids);
                    // notify children
                    this._bookingOutput.next(data);
                    return;
                }
                // handle request for updating single fields (reload)
                let has_change = false;
                if (booking.hasOwnProperty('customer_id') && booking.customer_id != this.booking.customer_id) {
                    yield this.updateCustomer(booking.customer_id);
                    has_change = true;
                }
                if ((booking.hasOwnProperty('payer_organisation_id') && booking.payer_organisation_id != this.booking.payer_organisation_id)
                    || (booking.hasOwnProperty('has_payer_organisation') && booking.has_payer_organisation != this.booking.has_payer_organisation)) {
                    yield this.updatePayer(booking.payer_organisation_id);
                    has_change = true;
                }
                if (booking.hasOwnProperty('center_id') && booking.center_id != this.booking.center_id) {
                    yield this.updateCenter(booking.center_id);
                    has_change = true;
                }
                if (booking.hasOwnProperty('type_id') && booking.type_id != this.booking.type_id) {
                    yield this.updateType(booking.type_id);
                    has_change = true;
                }
                if (booking.hasOwnProperty('description') && booking.description != this.booking.description) {
                    yield this.updateDescription(booking.description);
                    has_change = true;
                }
                if (booking.hasOwnProperty('contacts_ids') && booking.contacts_ids.length != this.booking.contacts_ids.length) {
                    this.booking.contacts_ids = booking.contacts_ids;
                    has_change = true;
                }
                if (booking.hasOwnProperty('booking_lines_groups_ids') && booking.booking_lines_groups_ids.length != this.booking.booking_lines_groups_ids.length) {
                    this.booking.booking_lines_groups_ids = booking.booking_lines_groups_ids;
                    has_change = true;
                }
                if (has_change) {
                    // reload booking
                    let data = yield this.load(Object.getOwnPropertyNames(new Booking()));
                    // update local object
                    for (let field of Object.keys(data)) {
                        this.booking[field] = data[field];
                    }
                    // relay changes to children components
                    this._bookingOutput.next(this.booking);
                    // notify User
                    this.snack.open("Réservation mise à jour");
                }
            }
            catch (response) {
                console.warn('some changes could not be stored', response);
                let error = 'unknonw';
                if (response && response.hasOwnProperty('error') && response['error'].hasOwnProperty('errors')) {
                    let errors = response['error']['errors'];
                    if (errors.hasOwnProperty('INVALID_PARAM')) {
                        error = 'invalid_param';
                    }
                    else if (errors.hasOwnProperty('NOT_ALLOWED')) {
                        error = 'not_allowed';
                    }
                    else if (errors.hasOwnProperty('CONFLICT_OBJECT')) {
                        error = 'conflict_object';
                    }
                }
                switch (error) {
                    case 'not_allowed':
                        this.snack.open("Erreur - Vous n'avez pas les autorisations pour cette opération.");
                        break;
                    case 'conflict_object':
                        this.snack.open("Erreur - Cette réservation a été modifiée par un autre utilisateur.");
                        break;
                    case 'unknonw':
                    case 'invalid_param':
                    default:
                        this.snack.open("Erreur - certains changements n'ont pas pu être enregistrés.");
                }
            }
        });
    }
    updateCustomer(customer_id) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditComponent::updateCustomer', customer_id);
            yield this.api.update("lodging\\sale\\booking\\Booking", [this.id], { "customer_id": customer_id });
        });
    }
    updateDescription(description) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditComponent::updateDescription', description);
            yield this.api.update("lodging\\sale\\booking\\Booking", [this.id], { "description": description });
        });
    }
    updatePayer(payer_organisation_id) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditComponent::updatePayer', payer_organisation_id);
            let values = {};
            if (payer_organisation_id <= 0) {
                values.has_payer_organisation = false;
                values.payer_organisation_id = 0;
            }
            else {
                values.has_payer_organisation = true;
                values.payer_organisation_id = payer_organisation_id;
            }
            yield this.api.update("lodging\\sale\\booking\\Booking", [this.id], values);
        });
    }
    updateCenter(center_id) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditComponent::updateCenter', center_id);
            yield this.api.update("lodging\\sale\\booking\\Booking", [this.id], { "center_id": center_id });
        });
    }
    updateType(type_id) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_5__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditComponent::updateType', type_id);
            yield this.api.update("lodging\\sale\\booking\\Booking", [this.id], { "type_id": type_id });
        });
    }
}
BookingEditComponent.ɵfac = function BookingEditComponent_Factory(t) { return new (t || BookingEditComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_6__.AuthService), _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_6__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdirectiveInject"](_angular_router__WEBPACK_IMPORTED_MODULE_7__.Router), _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialog), _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdirectiveInject"](_angular_router__WEBPACK_IMPORTED_MODULE_7__.ActivatedRoute), _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdirectiveInject"](_angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_9__.MatSnackBar), _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_3__.NgZone)); };
BookingEditComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵdefineComponent"]({ type: BookingEditComponent, selectors: [["booking-edit"]], decls: 19, vars: 8, consts: [[1, "container"], [1, "booking-header"], [1, "booking-body"], ["linear", ""], ["stepper", ""], ["matStepLabel", ""], [1, "step-container"], [3, "bookingInput", "bookingOutput"], ["id", "sb-booking-container", 1, "sb-container"]], template: function BookingEditComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](0, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](1, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵtext"](2, "Nouvelle r\u00E9servation");
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](3, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](4, "mat-horizontal-stepper", 3, 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](6, "mat-step");
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵtemplate"](7, BookingEditComponent_ng_template_7_Template, 1, 0, "ng-template", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](8, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelement"](9, "booking-edit-customer", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](10, "mat-step");
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵtemplate"](11, BookingEditComponent_ng_template_11_Template, 1, 0, "ng-template", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](12, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelement"](13, "booking-edit-sojourn", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](14, "mat-step");
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵtemplate"](15, BookingEditComponent_ng_template_15_Template, 1, 0, "ng-template", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementStart"](16, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelement"](17, "booking-edit-bookings", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵelement"](18, "div", 8);
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵclassProp"]("hidden", ctx.showSbContainer);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵadvance"](9);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵproperty"]("bookingInput", ctx._bookingOutput)("bookingOutput", ctx._bookingInput);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵadvance"](4);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵproperty"]("bookingInput", ctx._bookingOutput)("bookingOutput", ctx._bookingInput);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵadvance"](4);
        _angular_core__WEBPACK_IMPORTED_MODULE_3__["ɵɵproperty"]("bookingInput", ctx._bookingOutput)("bookingOutput", ctx._bookingInput);
    } }, directives: [_angular_material_stepper__WEBPACK_IMPORTED_MODULE_10__.MatHorizontalStepper, _angular_material_stepper__WEBPACK_IMPORTED_MODULE_10__.MatStep, _angular_material_stepper__WEBPACK_IMPORTED_MODULE_10__.MatStepLabel, _components_booking_edit_customer_booking_edit_customer_component__WEBPACK_IMPORTED_MODULE_0__.BookingEditCustomerComponent, _components_booking_edit_sojourn_booking_edit_sojourn_component__WEBPACK_IMPORTED_MODULE_1__.BookingEditSojournComponent, _components_booking_edit_bookings_booking_edit_bookings_component__WEBPACK_IMPORTED_MODULE_2__.BookingEditBookingsComponent], styles: ["[_nghost-%COMP%] {\n  width: 100%;\n  height: 100%;\n  overflow: hidden;\n  box-sizing: border-box;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%] {\n  height: 100%;\n  width: 100%;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-header[_ngcontent-%COMP%] {\n  width: 100%;\n  padding-left: 12px;\n  height: 48px;\n  line-height: 48px;\n  border-bottom: solid 1px lightgrey;\n  font-size: 22px;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-body[_ngcontent-%COMP%] {\n  height: calc(100vh - 123px);\n  width: 100%;\n  overflow-y: scroll;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-body[_ngcontent-%COMP%]::-webkit-scrollbar {\n  width: 6px;\n  overflow-y: scroll;\n  background: transparent;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%]   .booking-body[_ngcontent-%COMP%]::-webkit-scrollbar-thumb {\n  background: var(--mdc-theme-primary, #6200ee);\n  border-radius: 10px;\n}\n[_nghost-%COMP%]   .container.hidden[_ngcontent-%COMP%] {\n  display: none;\n}\n[_nghost-%COMP%]   .step-container[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: row;\n  height: 100%;\n  overflow-x: hidden;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5jb21wb25lbnQuc2NzcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUVJLFdBQUE7RUFDQSxZQUFBO0VBQ0EsZ0JBQUE7RUFDQSxzQkFBQTtBQUFKO0FBRUk7RUFDSSxZQUFBO0VBQ0EsV0FBQTtBQUFSO0FBRVE7RUFDSSxXQUFBO0VBQ0Esa0JBQUE7RUFDQSxZQUFBO0VBQ0EsaUJBQUE7RUFDQSxrQ0FBQTtFQUNBLGVBQUE7QUFBWjtBQUdRO0VBQ0ksMkJBQUE7RUFDQSxXQUFBO0VBQ0Esa0JBQUE7QUFEWjtBQUlRO0VBQ0ksVUFBQTtFQUNBLGtCQUFBO0VBQ0EsdUJBQUE7QUFGWjtBQU1RO0VBQ0ksNkNBQUE7RUFDQSxtQkFBQTtBQUpaO0FBUUk7RUFDSSxhQUFBO0FBTlI7QUFTSTtFQUNJLGFBQUE7RUFDQSxtQkFBQTtFQUNBLFlBQUE7RUFDQSxrQkFBQTtBQVBSIiwiZmlsZSI6ImJvb2tpbmcuZWRpdC5jb21wb25lbnQuc2NzcyIsInNvdXJjZXNDb250ZW50IjpbIjpob3N0IHtcclxuXHJcbiAgICB3aWR0aDogMTAwJTtcclxuICAgIGhlaWdodDogMTAwJTtcclxuICAgIG92ZXJmbG93OiBoaWRkZW47XHJcbiAgICBib3gtc2l6aW5nOiBib3JkZXItYm94O1xyXG5cclxuICAgIC5jb250YWluZXIge1xyXG4gICAgICAgIGhlaWdodDogMTAwJTtcclxuICAgICAgICB3aWR0aDogMTAwJTtcclxuXHJcbiAgICAgICAgLmJvb2tpbmctaGVhZGVyIHtcclxuICAgICAgICAgICAgd2lkdGg6IDEwMCU7XHJcbiAgICAgICAgICAgIHBhZGRpbmctbGVmdDogMTJweDtcclxuICAgICAgICAgICAgaGVpZ2h0OiA0OHB4O1xyXG4gICAgICAgICAgICBsaW5lLWhlaWdodDogNDhweDtcclxuICAgICAgICAgICAgYm9yZGVyLWJvdHRvbTogc29saWQgMXB4IGxpZ2h0Z3JleTtcclxuICAgICAgICAgICAgZm9udC1zaXplOiAyMnB4O1xyXG4gICAgICAgIH1cclxuICAgIFxyXG4gICAgICAgIC5ib29raW5nLWJvZHkge1xyXG4gICAgICAgICAgICBoZWlnaHQ6IGNhbGMoMTAwdmggLSAxMjNweCk7XHJcbiAgICAgICAgICAgIHdpZHRoOiAxMDAlO1xyXG4gICAgICAgICAgICBvdmVyZmxvdy15OiBzY3JvbGw7XHJcbiAgICAgICAgfVxyXG4gICAgXHJcbiAgICAgICAgLmJvb2tpbmctYm9keTo6LXdlYmtpdC1zY3JvbGxiYXIge1xyXG4gICAgICAgICAgICB3aWR0aDogNnB4O1xyXG4gICAgICAgICAgICBvdmVyZmxvdy15OiBzY3JvbGw7XHJcbiAgICAgICAgICAgIGJhY2tncm91bmQ6IHRyYW5zcGFyZW50O1xyXG4gICAgICAgIFxyXG4gICAgICAgIH1cclxuICAgICAgICBcclxuICAgICAgICAuYm9va2luZy1ib2R5Ojotd2Via2l0LXNjcm9sbGJhci10aHVtYiB7XHJcbiAgICAgICAgICAgIGJhY2tncm91bmQ6IHZhcigtLW1kYy10aGVtZS1wcmltYXJ5LCAjNjIwMGVlKTtcclxuICAgICAgICAgICAgYm9yZGVyLXJhZGl1czogMTBweDtcclxuICAgICAgICB9XHJcbiAgICB9XHJcblxyXG4gICAgLmNvbnRhaW5lci5oaWRkZW4ge1xyXG4gICAgICAgIGRpc3BsYXk6IG5vbmU7XHJcbiAgICB9XHJcblxyXG4gICAgLnN0ZXAtY29udGFpbmVyIHtcclxuICAgICAgICBkaXNwbGF5OiBmbGV4OyBcclxuICAgICAgICBmbGV4LWRpcmVjdGlvbjogcm93OyBcclxuICAgICAgICBoZWlnaHQ6IDEwMCU7IFxyXG4gICAgICAgIG92ZXJmbG93LXg6IGhpZGRlbjtcclxuICAgIH1cclxuICAgIFxyXG59Il19 */"] });


/***/ }),

/***/ 6892:
/*!******************************************************************************************************!*\
  !*** ./src/app/in/bookings/edit/components/booking.edit.bookings/booking.edit.bookings.component.ts ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditBookingsComponent": () => (/* binding */ BookingEditBookingsComponent)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! rxjs */ 8229);
/* harmony import */ var _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @angular/cdk/drag-drop */ 7310);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @angular/material/snack-bar */ 7001);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_icon__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @angular/material/icon */ 6627);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _components_booking_edit_bookings_group_booking_edit_bookings_group_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/booking.edit.bookings.group/booking.edit.bookings.group.component */ 6340);











function BookingEditBookingsComponent_div_11_div_2_Template(rf, ctx) { if (rf & 1) {
    const _r5 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 10);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](2, "mat-icon", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](3, "drag_indicator");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](4, "div", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](5, "button", 14);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsComponent_div_11_div_2_Template_button_click_5_listener() { const restoredCtx = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r5); const group_r2 = restoredCtx.$implicit; const ctx_r4 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2); return ctx_r4.vm.groups.remove(group_r2); });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](6, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](7, "delete");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](8, "booking-edit-bookings-group", 15);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const group_r2 = ctx.$implicit;
    const index_r3 = ctx.index;
    const ctx_r1 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("cdkDragData", group_r2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](8);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("bookingInput", ctx_r1.booking)("groupInput", ctx_r1._groupOutput[index_r3])("groupOutput", ctx_r1._groupInput);
} }
function BookingEditBookingsComponent_div_11_Template(rf, ctx) { if (rf & 1) {
    const _r7 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 8);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("cdkDropListDropped", function BookingEditBookingsComponent_div_11_Template_div_cdkDropListDropped_1_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r7); const ctx_r6 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](); return ctx_r6.vm.groups.drop($event); });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](2, BookingEditBookingsComponent_div_11_div_2_Template, 9, 4, "div", 9);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngForOf", ctx_r0.groups);
} }
class BookingEditBookingsComponent {
    constructor(api, auth, zone, snack) {
        this.api = api;
        this.auth = auth;
        this.zone = zone;
        this.snack = snack;
        // observable for updates from children components
        this._groupInput = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
        // array of observable for children components
        this._groupOutput = [];
        this.model_fields = {
            BookingLineGroup: ["id", "booking_id", "name", "order", "has_pack", "pack_id", "price",
                "is_locked", "date_from", "date_to",
                "sojourn_type", "nb_pers", "rate_class_id",
                "booking_lines_ids", "accomodations_ids"]
        };
        this.booking = {};
        this.center = null;
        this.groups = [];
        this.vm = {
            price: {
                value: 0.0
            },
            groups: {
                add: () => this.groupAdd(),
                remove: (group) => this.groupRemove(group),
                drop: (event) => this.groupDrop(event)
            }
        };
    }
    ngOnInit() {
        // listen to the parent for changes on booking object
        this.bookingInput.subscribe((booking) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () { return this.load(booking); }));
        // listen to changes relayed by children component on the _bookingInput observable
        this._groupInput.subscribe(params => this.updateFromGroup(params));
    }
    /**
     * Load subitem of current booking object.
     *
     * @param booking
     */
    load(booking) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            this.zone.run(() => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
                try {
                    console.log("BookingEditBookingsComponent: received changes from parent", booking);
                    // update local booking object
                    for (let field of Object.keys(booking)) {
                        this.booking[field] = booking[field];
                    }
                    if (booking.hasOwnProperty('price')) {
                        this.vm.price.value = booking.price;
                    }
                    if (booking.booking_lines_groups_ids && booking.booking_lines_groups_ids.length) {
                        let data = yield this.loadGroups(booking.booking_lines_groups_ids, this.model_fields['BookingLineGroup']);
                        if (data) {
                            for (let [index, group] of data.entries()) {
                                // add new lines (indexes from this.lines and this._lineOutput are synced)
                                if (index >= this.groups.length) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
                                    this._groupOutput.push(item);
                                    item.next(group);
                                    this.groups.push(group);
                                }
                                // if lines differ, overwrite previsously assigned line
                                else if (JSON.stringify(this.groups[index]) != JSON.stringify(group)) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
                                    this._groupOutput[index] = item;
                                    this.groups[index] = group;
                                    item.next(group);
                                }
                            }
                            // remove remaining lines, if any
                            if (this.groups.length > data.length) {
                                this.groups.splice(data.length);
                                this._groupOutput.splice(data.length);
                            }
                        }
                    }
                    if (booking.center_id) {
                        let data = yield this.api.read("lodging\\identity\\Center", [booking.center_id], ["id", "name", "code", "organisation_id"]);
                        if (data && data.length) {
                            this.center = data[0];
                        }
                    }
                }
                catch (response) {
                    console.warn(response);
                }
            }));
        });
    }
    loadGroups(ids, fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            let data = yield this.api.read("lodging\\sale\\booking\\BookingLineGroup", ids, fields, 'order');
            return data;
        });
    }
    groupAdd() {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log("group add");
            try {
                const group = yield this.api.create("lodging\\sale\\booking\\BookingLineGroup", {
                    name: "Séjour " + this.center.name,
                    order: this.groups.length + 1,
                    booking_id: this.booking.id,
                    rate_class_id: 4 // default to 'general public'
                });
                let data = yield this.loadGroups([group.id], this.model_fields['BookingLineGroup']);
                let item = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
                this._groupOutput.push(item);
                this.groups.push(data[0]);
                item.next(data[0]);
                // emit change to parent
                //this.bookingOutput.next({booking_lines_groups_ids: groups_ids});
            }
            catch (error) {
                console.log(error);
            }
        });
    }
    groupRemove(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            try {
                const response = yield this.api.remove("lodging\\sale\\booking\\BookingLineGroup", [group.id], true);
                let index = this.groups.findIndex((element) => element.id == group.id);
                this.groups.splice(index, 1);
                this._groupOutput.splice(index, 1);
            }
            catch (response) {
                console.warn(response);
            }
        });
    }
    groupDrop(event) {
        console.log(event.previousIndex, event.currentIndex);
        // adapt this.groups and this._groupOutput
        (0,_angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_4__.moveItemInArray)(this.groups, event.previousIndex, event.currentIndex);
        (0,_angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_4__.moveItemInArray)(this._groupOutput, event.previousIndex, event.currentIndex);
        // adapt new values for 'order' field
        for (let index in this.groups) {
            let item = this.groups[index];
            this.updateFromGroup({ id: item.id, order: parseInt(index) + 1 });
        }
    }
    updateFromGroup(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log("BookingEditBookingsComponent: received changes from child", group);
            try {
                let index = this.groups.findIndex((element) => element.id == group.id);
                let t_group = this.groups.find((element) => element.id == group.id);
                // handle single fields updates
                let has_change = false;
                let refresh_requests = [];
                if (group.hasOwnProperty('name') && group.name != t_group.name) {
                    yield this.updateName(group);
                    has_change = true;
                }
                if (group.hasOwnProperty('pack_id') && group.pack_id != t_group.pack_id) {
                    yield this.updatePack(group);
                    // we need to reload booking price
                    refresh_requests.push('price');
                    has_change = true;
                }
                if (group.hasOwnProperty('rate_class_id') && group.rate_class_id != t_group.rate_class_id) {
                    yield this.updateRateClass(group);
                    has_change = true;
                }
                if (group.hasOwnProperty('is_locked') && group.is_locked != t_group.is_locked) {
                    yield this.updateIsLocked(group);
                    // we need to reload booking price
                    refresh_requests.push('price');
                    has_change = true;
                }
                if (group.hasOwnProperty('has_pack') && group.has_pack != t_group.has_pack) {
                    yield this.updateHasPack(group);
                    has_change = true;
                }
                if ((group.hasOwnProperty('date_from') && group.date_from != t_group.date_from)
                    || (group.hasOwnProperty('date_to') && group.date_to != t_group.date_to)) {
                    yield this.updateDate(group);
                    has_change = true;
                }
                if (group.hasOwnProperty('nb_pers') && group.nb_pers != t_group.nb_pers) {
                    yield this.updateNbPers(group);
                    has_change = true;
                }
                if (group.hasOwnProperty('order') && group.order != t_group.order) {
                    yield this.updateOrder(group);
                    has_change = true;
                }
                if (group.hasOwnProperty('booking_lines_ids')) {
                    let diff = group.booking_lines_ids.filter((lid) => t_group.booking_lines_ids.indexOf(lid) === -1);
                    if (diff.length) {
                        yield this.updateBookingLinesIds(group);
                        has_change = true;
                    }
                }
                // handle explicit requests for updating single fields (reload partial object)
                if (group.hasOwnProperty('refresh')) {
                    // some changes have been done that might impact current object
                    // refresh property specifies which fields have to be re-loaded
                    let model_fields = this.model_fields['BookingLineGroup'];
                    if (group.refresh.hasOwnProperty('self')) {
                        if (Array.isArray(group.refresh.self)) {
                            model_fields = group.refresh.self;
                        }
                        // reload object from server
                        let data = yield this.loadGroups([group.id], model_fields);
                        this._groupOutput[index].next(data[0]);
                    }
                    // handle requests to relay to parent
                    if (group.refresh.hasOwnProperty('booking_id')) {
                        // group.refresh.booking_id is an array of fields from sale\booking\Booking to be updated
                        refresh_requests = [...refresh_requests, ...group.refresh.booking_id];
                    }
                }
                // reload whole object from server
                else if (has_change) {
                    let data = yield this.loadGroups([group.id], this.model_fields['BookingLineGroup']);
                    let object = data[0];
                    for (let field of Object.keys(object)) {
                        this.groups[index][field] = object[field];
                    }
                    // relay changes to children components
                    this._groupOutput[index].next(this.groups[index]);
                    // notify User
                    this.snack.open("Regroupement mis à jour");
                }
                // relay refresh request to parent, if any
                if (refresh_requests.length) {
                    this.bookingOutput.next({ id: this.booking.id, refresh: refresh_requests });
                }
            }
            catch (response) {
                console.warn('some changes could not be stored', response);
                let error = 'unknonw';
                if (response && response.hasOwnProperty('error') && response['error'].hasOwnProperty('errors')) {
                    let errors = response['error']['errors'];
                    if (errors.hasOwnProperty('INVALID_PARAM')) {
                        error = 'invalid_param';
                    }
                    else if (errors.hasOwnProperty('NOT_ALLOWED')) {
                        error = 'not_allowed';
                    }
                    else if (errors.hasOwnProperty('CONFLICT_OBJECT')) {
                        error = 'conflict_object';
                    }
                }
                switch (error) {
                    case 'not_allowed':
                        this.snack.open("Erreur - Vous n'avez pas les autorisations pour cette opération.");
                        break;
                    case 'conflict_object':
                        this.snack.open("Erreur - Cette réservation a été modifiée par un autre utilisateur.");
                        break;
                    case 'unknonw':
                    case 'invalid_param':
                    default:
                        this.snack.open("Erreur - certains changements n'ont pas pu être enregistrés.");
                }
            }
        });
    }
    ;
    updateName(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updateName', group);
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "name": group.name });
        });
    }
    updatePack(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updatePack', group);
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "pack_id": group.pack_id });
        });
    }
    updateRateClass(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updateRateClass', group);
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "rate_class_id": group.rate_class_id });
        });
    }
    updateHasPack(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updateHasPack', group);
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "has_pack": group.has_pack });
        });
    }
    updateNbPers(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updateNbPers', group);
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "nb_pers": group.nb_pers });
        });
    }
    updateOrder(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updateOrder', group);
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "order": group.order });
        });
    }
    updateIsLocked(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updateIsLocked', group);
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "is_locked": group.is_locked });
        });
    }
    updateDate(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            console.log('BookingEditBookingsComponent::updateDate', group);
            if (group.date_from && group.date_to) {
                yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "date_from": group.date_from, "date_to": group.date_to });
            }
            else if (group.date_from) {
                yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "date_from": group.date_from });
            }
            else {
                yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "date_to": group.date_to });
            }
        });
    }
    updateBookingLinesIds(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            yield this.api.update("lodging\\sale\\booking\\BookingLineGroup", [group.id], { "booking_lines_ids": group.booking_lines_ids });
        });
    }
}
BookingEditBookingsComponent.ɵfac = function BookingEditBookingsComponent_Factory(t) { return new (t || BookingEditBookingsComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_5__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_5__.AuthService), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_1__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_6__.MatSnackBar)); };
BookingEditBookingsComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdefineComponent"]({ type: BookingEditBookingsComponent, selectors: [["booking-edit-bookings"]], inputs: { bookingInput: "bookingInput", bookingOutput: "bookingOutput" }, decls: 12, vars: 5, consts: [[1, "container"], [1, "outer-wrapper", 2, "width", "100%"], [1, "header", 2, "display", "flex", "width", "100%"], [2, "flex", "1"], ["mat-mini-fab", "", "color", "primary", 2, "transform", "scale(0.65)", 3, "click"], [2, "margin-left", "auto", "font-size", "20px"], ["class", "inner-wrapper", 4, "ngIf"], [1, "inner-wrapper"], ["cdkDropList", "", 1, "groups-list", 3, "cdkDropListDropped"], ["class", "group-item", "cdkDrag", "", 3, "cdkDragData", 4, "ngFor", "ngForOf"], ["cdkDrag", "", 1, "group-item", 3, "cdkDragData"], ["cdkDragHandle", "", 1, "group-handle"], [2, "font-size", "16px"], [1, "group-remove"], ["mat-icon-button", "", 3, "click"], [3, "bookingInput", "groupInput", "groupOutput"]], template: function BookingEditBookingsComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](2, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](3, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](4, "Ajouter un s\u00E9jour ");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](5, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsComponent_Template_button_click_5_listener() { return ctx.vm.groups.add(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](6, "mat-icon");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](7, "add");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](8, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](9);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](10, "number");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](11, BookingEditBookingsComponent_div_11_Template, 3, 1, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](9);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate1"]("Total TTC ", _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind2"](10, 2, ctx.vm.price.value, "1.2-2"), " \u20AC");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.groups.length);
    } }, directives: [_angular_material_button__WEBPACK_IMPORTED_MODULE_7__.MatButton, _angular_material_icon__WEBPACK_IMPORTED_MODULE_8__.MatIcon, _angular_common__WEBPACK_IMPORTED_MODULE_9__.NgIf, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_4__.CdkDropList, _angular_common__WEBPACK_IMPORTED_MODULE_9__.NgForOf, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_4__.CdkDrag, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_4__.CdkDragHandle, _components_booking_edit_bookings_group_booking_edit_bookings_group_component__WEBPACK_IMPORTED_MODULE_0__.BookingEditBookingsGroupComponent], pipes: [_angular_common__WEBPACK_IMPORTED_MODULE_9__.DecimalPipe], styles: ["[_nghost-%COMP%] {\n  width: 100%;\n  height: 100%;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%] {\n  display: flex;\n  height: 100%;\n  width: 100%;\n}\n[_nghost-%COMP%]   .inner-wrapper[_ngcontent-%COMP%] {\n  border: solid 1px black;\n}\n[_nghost-%COMP%]   .group-item[_ngcontent-%COMP%] {\n  position: relative;\n  margin: 12px;\n  padding-bottom: 12px;\n  background-color: rgba(0, 0, 0, 0.04);\n}\n[_nghost-%COMP%]   .group-item[_ngcontent-%COMP%]   .group-handle[_ngcontent-%COMP%] {\n  position: absolute;\n  top: 10px;\n  left: 5px;\n  z-index: 3;\n}\n[_nghost-%COMP%]   .group-item[_ngcontent-%COMP%]   .group-remove[_ngcontent-%COMP%] {\n  position: absolute;\n  right: 10px;\n  top: 10px;\n}\n.cdk-drag-preview[_ngcontent-%COMP%] {\n  box-sizing: border-box;\n  border-radius: 4px;\n  box-shadow: 0 5px 5px -3px rgba(0, 0, 0, 0.2), 0 8px 10px 1px rgba(0, 0, 0, 0.14), 0 3px 14px 2px rgba(0, 0, 0, 0.12);\n  background-color: lightgrey !important;\n}\n.cdk-drag-preview[_ngcontent-%COMP%]   .group-handle[_ngcontent-%COMP%] {\n  display: none;\n}\n.cdk-drag-preview[_ngcontent-%COMP%]   .group-remove[_ngcontent-%COMP%] {\n  display: none;\n}\n.cdk-drag-placeholder[_ngcontent-%COMP%] {\n  opacity: 0;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5ib29raW5ncy5jb21wb25lbnQuc2NzcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUVFLFdBQUE7RUFDQSxZQUFBO0FBQUY7QUFHRTtFQUNFLGFBQUE7RUFDQSxZQUFBO0VBQ0EsV0FBQTtBQURKO0FBS0U7RUFDRSx1QkFBQTtBQUhKO0FBTUU7RUFDRSxrQkFBQTtFQUNBLFlBQUE7RUFDQSxvQkFBQTtFQUNBLHFDQUFBO0FBSko7QUFNSTtFQUNFLGtCQUFBO0VBQ0EsU0FBQTtFQUNBLFNBQUE7RUFDQSxVQUFBO0FBSk47QUFPSTtFQUNFLGtCQUFBO0VBQ0EsV0FBQTtFQUNBLFNBQUE7QUFMTjtBQVlBO0VBQ0Usc0JBQUE7RUFDQSxrQkFBQTtFQUNBLHFIQUFBO0VBR0Esc0NBQUE7QUFYRjtBQWFFO0VBQ0UsYUFBQTtBQVhKO0FBY0U7RUFDRSxhQUFBO0FBWko7QUFnQkE7RUFDRSxVQUFBO0FBYkYiLCJmaWxlIjoiYm9va2luZy5lZGl0LmJvb2tpbmdzLmNvbXBvbmVudC5zY3NzIiwic291cmNlc0NvbnRlbnQiOlsiOmhvc3Qge1xyXG5cclxuICB3aWR0aDogMTAwJTtcclxuICBoZWlnaHQ6IDEwMCU7XHJcblxyXG5cclxuICAuY29udGFpbmVyIHtcclxuICAgIGRpc3BsYXk6IGZsZXg7XHJcbiAgICBoZWlnaHQ6IDEwMCU7XHJcbiAgICB3aWR0aDogMTAwJTtcclxuICB9XHJcblxyXG5cclxuICAuaW5uZXItd3JhcHBlciB7XHJcbiAgICBib3JkZXI6IHNvbGlkIDFweCBibGFjaztcclxuICB9XHJcblxyXG4gIC5ncm91cC1pdGVtIHtcclxuICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcclxuICAgIG1hcmdpbjogMTJweDtcclxuICAgIHBhZGRpbmctYm90dG9tOiAxMnB4O1xyXG4gICAgYmFja2dyb3VuZC1jb2xvcjogcmdiYSgwLCAwLCAwLCAwLjA0KTtcclxuXHJcbiAgICAuZ3JvdXAtaGFuZGxlIHtcclxuICAgICAgcG9zaXRpb246IGFic29sdXRlO1xyXG4gICAgICB0b3A6IDEwcHg7XHJcbiAgICAgIGxlZnQ6IDVweDtcclxuICAgICAgei1pbmRleDogMztcclxuICAgIH1cclxuXHJcbiAgICAuZ3JvdXAtcmVtb3ZlIHtcclxuICAgICAgcG9zaXRpb246IGFic29sdXRlO1xyXG4gICAgICByaWdodDogMTBweDtcclxuICAgICAgdG9wOiAxMHB4O1xyXG4gICAgfVxyXG4gIFxyXG4gIH1cclxuICAgIFxyXG59XHJcblxyXG4uY2RrLWRyYWctcHJldmlldyB7XHJcbiAgYm94LXNpemluZzogYm9yZGVyLWJveDtcclxuICBib3JkZXItcmFkaXVzOiA0cHg7XHJcbiAgYm94LXNoYWRvdzogMCA1cHggNXB4IC0zcHggcmdiYSgwLCAwLCAwLCAwLjIpLFxyXG4gICAgICAgICAgICAgIDAgOHB4IDEwcHggMXB4IHJnYmEoMCwgMCwgMCwgMC4xNCksXHJcbiAgICAgICAgICAgICAgMCAzcHggMTRweCAycHggcmdiYSgwLCAwLCAwLCAwLjEyKTtcclxuICBiYWNrZ3JvdW5kLWNvbG9yOiBsaWdodGdyZXkgIWltcG9ydGFudDtcclxuXHJcbiAgLmdyb3VwLWhhbmRsZSB7XHJcbiAgICBkaXNwbGF5OiBub25lO1xyXG4gIH1cclxuXHJcbiAgLmdyb3VwLXJlbW92ZSB7XHJcbiAgICBkaXNwbGF5OiBub25lO1xyXG4gIH1cclxufVxyXG5cclxuLmNkay1kcmFnLXBsYWNlaG9sZGVyIHtcclxuICBvcGFjaXR5OiAwO1xyXG59Il19 */"] });


/***/ }),

/***/ 5795:
/*!*****************************************************************************************************************************************************************************!*\
  !*** ./src/app/in/bookings/edit/components/booking.edit.bookings/components/booking.edit.bookings.group.accomodation/booking.edit.bookings.group.accomodation.component.ts ***!
  \*****************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditBookingsGroupAccomodationComponent": () => (/* binding */ BookingEditBookingsGroupAccomodationComponent)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! rxjs */ 8229);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! rxjs */ 9165);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! rxjs/operators */ 4395);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! rxjs/operators */ 8002);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! rxjs/operators */ 9773);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @angular/material/snack-bar */ 7001);
/* harmony import */ var _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @angular/material/form-field */ 8295);
/* harmony import */ var _angular_material_input__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @angular/material/input */ 3166);
/* harmony import */ var _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @angular/material/autocomplete */ 1554);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_icon__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @angular/material/icon */ 6627);
/* harmony import */ var _angular_material_core__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! @angular/material/core */ 7817);













function BookingEditBookingsGroupAccomodationComponent_button_7_Template(rf, ctx) { if (rf & 1) {
    const _r4 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "button", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditBookingsGroupAccomodationComponent_button_7_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r4); const ctx_r3 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r3.vm.rental_unit.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupAccomodationComponent_div_10_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option", 9);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const rental_unit_r8 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("value", rental_unit_r8);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate2"](" ", rental_unit_r8.name, " (", rental_unit_r8.capacity, ") ");
} }
function BookingEditBookingsGroupAccomodationComponent_div_10_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupAccomodationComponent_div_10_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](1, BookingEditBookingsGroupAccomodationComponent_div_10_mat_option_1_Template, 2, 3, "mat-option", 8);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](2, BookingEditBookingsGroupAccomodationComponent_div_10_mat_option_2_Template, 3, 0, "mat-option", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r5 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngForOf", list_r5);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", list_r5.length == 0);
} }
class BookingEditBookingsGroupAccomodationComponent {
    constructor(api, auth, zone, snack) {
        this.api = api;
        this.auth = auth;
        this.zone = zone;
        this.snack = snack;
        this.ready = false;
        this.accomodation = {};
        this.product = null;
        this.rental_unit = null;
        this.vm = {
            rental_unit: {
                name: '',
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_1__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_2__.Observable(),
                change: (event) => this.rentalUnitChange(event),
                inputChange: (event) => this.rentalUnitInputChange(event),
                focus: () => this.rentalUnitFocus(),
                restore: () => this.rentalUnitRestore(),
                reset: () => this.rentalUnitReset(),
                display: (type) => this.rentalUnitDisplay(type)
            },
            product: {
                id: 0,
                name: ''
            }
        };
    }
    ngOnInit() {
        this.accomodationInput.subscribe((line) => this.load(line));
        /**
         * listen to the changes on FormControl objects
         */
        this.vm.rental_unit.filteredList = this.vm.rental_unit.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_3__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_4__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_5__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_6__.__awaiter)(this, void 0, void 0, function* () { return this.filterRentalUnits(name); })));
    }
    /**
     * Assign values from parent and load sub-objects required by the view.
     *
     * @param accomodation
     */
    load(accomodation) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_6__.__awaiter)(this, void 0, void 0, function* () {
            this.zone.run(() => {
                this.ready = false;
            });
            this.zone.run(() => (0,tslib__WEBPACK_IMPORTED_MODULE_6__.__awaiter)(this, void 0, void 0, function* () {
                try {
                    // update local group object
                    for (let field of Object.keys(accomodation)) {
                        this.accomodation[field] = accomodation[field];
                    }
                    if (accomodation.product_id) {
                        let data = yield this.api.read("lodging\\sale\\catalog\\Product", [accomodation.product_id], ["id", "name", "sku"]);
                        if (data && data.length) {
                            let product = data[0];
                            this.product = product;
                            this.vm.product.name = product.name + ' (' + product.sku + ')';
                        }
                    }
                    if (accomodation.rental_unit_id) {
                        let data = yield this.api.read("lodging\\realestate\\RentalUnit", [accomodation.rental_unit_id], ["id", "name", "capacity"]);
                        if (data && data.length) {
                            let rental_unit = data[0];
                            this.rental_unit = rental_unit;
                            this.vm.rental_unit.name = rental_unit.name + ' (' + rental_unit.capacity + ')';
                        }
                    }
                }
                catch (response) {
                    console.warn(response);
                }
                this.ready = true;
            }));
        });
    }
    rentalUnitInputChange(event) {
        this.vm.rental_unit.inputClue.next(event.target.value);
    }
    rentalUnitFocus() {
        this.vm.rental_unit.inputClue.next("");
    }
    rentalUnitDisplay(rental_unit) {
        return rental_unit ? rental_unit.name + ' (' + rental_unit.capacity + ')' : '';
    }
    rentalUnitReset() {
        setTimeout(() => {
            this.vm.rental_unit.name = '';
        }, 100);
    }
    rentalUnitRestore() {
        if (this.rental_unit) {
            this.vm.rental_unit.name = this.rental_unit.name + ' (' + this.rental_unit.capacity + ')';
        }
        else {
            this.vm.rental_unit.name = '';
        }
    }
    rentalUnitChange(event) {
        console.log('BookingEditCustomerComponent::rentalUnitChange', event);
        // from mat-autocomplete
        if (event && event.option && event.option.value) {
            let rental_unit = event.option.value;
            this.rental_unit = rental_unit;
            this.vm.rental_unit.name = rental_unit.name + ' (' + rental_unit.capacity + ')';
            // relay change to parent component
            this.accomodationOutput.next({ id: this.accomodation.id, rental_unit_id: rental_unit.id });
        }
    }
    filterRentalUnits(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_6__.__awaiter)(this, void 0, void 0, function* () {
            let filtered = [];
            try {
                let data = yield this.api.collect("lodging\\realestate\\RentalUnit", [["name", "ilike", '%' + name + '%']], ["id", "name", "capacity"], 'name', 'asc', 0, 25);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
}
BookingEditBookingsGroupAccomodationComponent.ɵfac = function BookingEditBookingsGroupAccomodationComponent_Factory(t) { return new (t || BookingEditBookingsGroupAccomodationComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__.AuthService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_0__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_8__.MatSnackBar)); };
BookingEditBookingsGroupAccomodationComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: BookingEditBookingsGroupAccomodationComponent, selectors: [["booking-edit-bookings-group-accomodation"]], inputs: { groupInput: "groupInput", accomodationOutput: "accomodationOutput", accomodationInput: "accomodationInput" }, decls: 12, vars: 9, consts: [[1, "cell"], ["matInput", "", "type", "text", "placeholder", "Commencez \u00E0 taper le nom", 3, "matAutocomplete", "value", "keyup", "focus", "blur"], [3, "align"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click", 4, "ngIf"], [3, "displayWith", "optionSelected"], ["rentalUnitAutocomplete", "matAutocomplete"], [4, "ngIf"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click"], [3, "value", 4, "ngFor", "ngForOf"], [3, "value"]], template: function BookingEditBookingsGroupAccomodationComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](3, "Unit\u00E9 locative");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "input", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("keyup", function BookingEditBookingsGroupAccomodationComponent_Template_input_keyup_4_listener($event) { return ctx.vm.rental_unit.inputChange($event); })("focus", function BookingEditBookingsGroupAccomodationComponent_Template_input_focus_4_listener() { return ctx.vm.rental_unit.focus(); })("blur", function BookingEditBookingsGroupAccomodationComponent_Template_input_blur_4_listener() { return ctx.vm.rental_unit.restore(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](5, "mat-hint", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](7, BookingEditBookingsGroupAccomodationComponent_button_7_Template, 3, 0, "button", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](8, "mat-autocomplete", 4, 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("optionSelected", function BookingEditBookingsGroupAccomodationComponent_Template_mat_autocomplete_optionSelected_8_listener($event) { return ctx.vm.rental_unit.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](10, BookingEditBookingsGroupAccomodationComponent_div_10_Template, 3, 2, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipe"](11, "async");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    } if (rf & 2) {
        const _r1 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](9);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("matAutocomplete", _r1)("value", ctx.vm.rental_unit.name);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("align", "end");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"]("Logement assign\u00E9 \u00E0 ", ctx.vm.product.name, "");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx.vm.rental_unit.name.length);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("displayWith", ctx.vm.rental_unit.display);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipeBind1"](11, 7, ctx.vm.rental_unit.filteredList));
    } }, directives: [_angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatFormField, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatLabel, _angular_material_input__WEBPACK_IMPORTED_MODULE_10__.MatInput, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_11__.MatAutocompleteTrigger, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatHint, _angular_common__WEBPACK_IMPORTED_MODULE_12__.NgIf, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_11__.MatAutocomplete, _angular_material_button__WEBPACK_IMPORTED_MODULE_13__.MatButton, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatSuffix, _angular_material_icon__WEBPACK_IMPORTED_MODULE_14__.MatIcon, _angular_common__WEBPACK_IMPORTED_MODULE_12__.NgForOf, _angular_material_core__WEBPACK_IMPORTED_MODULE_15__.MatOption], pipes: [_angular_common__WEBPACK_IMPORTED_MODULE_12__.AsyncPipe], styles: ["[_nghost-%COMP%] {\n  display: flex;\n  width: 100%;\n  height: 100%;\n}\n[_nghost-%COMP%]   .hidden[_ngcontent-%COMP%] {\n  visibility: hidden;\n}\n[_nghost-%COMP%]   .cell[_ngcontent-%COMP%] {\n  flex: 0 1 30%;\n}\n[_nghost-%COMP%]   .cell[_ngcontent-%COMP%]   mat-form-field[_ngcontent-%COMP%] {\n  width: 100%;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5ib29raW5ncy5ncm91cC5hY2NvbW9kYXRpb24uY29tcG9uZW50LnNjc3MiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7RUFDRSxhQUFBO0VBQ0EsV0FBQTtFQUNBLFlBQUE7QUFDRjtBQUNFO0VBQ0Usa0JBQUE7QUFDSjtBQUVFO0VBQ0UsYUFBQTtBQUFKO0FBRUk7RUFDRSxXQUFBO0FBQU4iLCJmaWxlIjoiYm9va2luZy5lZGl0LmJvb2tpbmdzLmdyb3VwLmFjY29tb2RhdGlvbi5jb21wb25lbnQuc2NzcyIsInNvdXJjZXNDb250ZW50IjpbIjpob3N0IHtcclxuICBkaXNwbGF5OiBmbGV4O1xyXG4gIHdpZHRoOiAxMDAlO1xyXG4gIGhlaWdodDogMTAwJTtcclxuXHJcbiAgLmhpZGRlbiB7XHJcbiAgICB2aXNpYmlsaXR5OiBoaWRkZW47XHJcbiAgfVxyXG5cclxuICAuY2VsbCB7XHJcbiAgICBmbGV4OiAwIDEgMzAlO1xyXG5cclxuICAgIG1hdC1mb3JtLWZpZWxkIHtcclxuICAgICAgd2lkdGg6IDEwMCU7XHJcbiAgICB9XHJcbiAgfVxyXG4gIFxyXG59Il19 */"] });


/***/ }),

/***/ 6628:
/*!*******************************************************************************************************************************************************************************!*\
  !*** ./src/app/in/bookings/edit/components/booking.edit.bookings/components/booking.edit.bookings.group.line.discount/booking.edit.bookings.group.line.discount.component.ts ***!
  \*******************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditBookingsGroupLineDiscountComponent": () => (/* binding */ BookingEditBookingsGroupLineDiscountComponent)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var _angular_forms__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @angular/forms */ 3679);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_dialog__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @angular/material/dialog */ 2238);
/* harmony import */ var _angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @angular/material/snack-bar */ 7001);
/* harmony import */ var _angular_material_form_field__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @angular/material/form-field */ 8295);
/* harmony import */ var _angular_material_slide_toggle__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @angular/material/slide-toggle */ 5396);
/* harmony import */ var _angular_material_input__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @angular/material/input */ 3166);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @angular/common */ 8583);











function BookingEditBookingsGroupLineDiscountComponent_mat_error_9_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-error");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, " Ne peut \u00EAtre vide. ");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
class BookingEditBookingsGroupLineDiscountComponent {
    constructor(api, auth, dialog, zone, snack) {
        this.api = api;
        this.auth = auth;
        this.dialog = dialog;
        this.zone = zone;
        this.snack = snack;
        this.ready = false;
        this.discount = {};
        this.vm = {
            value: {
                value: 0.0,
                formControl: new _angular_forms__WEBPACK_IMPORTED_MODULE_1__.FormControl('', _angular_forms__WEBPACK_IMPORTED_MODULE_1__.Validators.required),
                change: (event) => this.valueChange(event)
            },
            type: {
                value: '',
                formControl: new _angular_forms__WEBPACK_IMPORTED_MODULE_1__.FormControl('', _angular_forms__WEBPACK_IMPORTED_MODULE_1__.Validators.required),
                change: (event) => this.typeChange(event)
            }
        };
    }
    ngOnInit() {
        this.discountInput.subscribe((discount) => this.load(discount));
        /**
         * listen to the changes on FormControl objects
         */
        this.vm.value.formControl.valueChanges.subscribe((value) => {
            this.vm.value.value = value;
        });
    }
    /**
     * Assign values from parent and load sub-objects required by the view.
     *
     * @param discount
     */
    load(discount) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_2__.__awaiter)(this, void 0, void 0, function* () {
            this.zone.run(() => {
                this.ready = false;
            });
            this.zone.run(() => (0,tslib__WEBPACK_IMPORTED_MODULE_2__.__awaiter)(this, void 0, void 0, function* () {
                try {
                    console.log("BookingEditBookingsGroupLineDiscountComponent: received changes from parent", discount.id, discount);
                    // update local group object
                    for (let field of Object.keys(discount)) {
                        this.discount[field] = discount[field];
                    }
                    if (discount.hasOwnProperty('type')) {
                        this.vm.type.value = discount.type;
                        if (discount.type == 'percent') {
                            this.vm.type.formControl.setValue(false);
                        }
                        else {
                            this.vm.type.formControl.setValue(true);
                        }
                    }
                    if (discount.hasOwnProperty('value')) {
                        this.vm.value.value = discount.value;
                        this.vm.value.formControl.setValue(discount.value);
                    }
                }
                catch (response) {
                    console.warn(response);
                }
                this.ready = true;
            }));
        });
    }
    typeChange(event) {
        console.log(event);
        // true is €, false, is %
        if (event) {
            this.vm.type.value = "amount";
        }
        else {
            this.vm.type.value = "percent";
        }
        if (this.vm.value.value) {
            this.discountOutput.next({ id: this.discount.id, type: this.vm.type.value });
        }
    }
    valueChange(event) {
        if (this.vm.value.value) {
            // relay change to parent component
            this.discountOutput.next({ id: this.discount.id, value: this.vm.value.value });
        }
    }
}
BookingEditBookingsGroupLineDiscountComponent.ɵfac = function BookingEditBookingsGroupLineDiscountComponent_Factory(t) { return new (t || BookingEditBookingsGroupLineDiscountComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_3__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_3__.AuthService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_4__.MatDialog), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_0__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_5__.MatSnackBar)); };
BookingEditBookingsGroupLineDiscountComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: BookingEditBookingsGroupLineDiscountComponent, selectors: [["booking-edit-bookings-group-line-discount"]], inputs: { lineInput: "lineInput", lineOutput: "lineOutput", discountInput: "discountInput", discountOutput: "discountOutput" }, decls: 10, vars: 3, consts: [[1, "cell"], ["floatLabel", "always", 1, "invisible", 2, "max-width", "85px"], [3, "formControl", "change"], ["matInput", "", "hidden", ""], [2, "max-width", "90px"], ["type", "number", "placeholder", "r\u00E9duction", "matInput", "", 3, "formControl", "blur"], [4, "ngIf"]], template: function BookingEditBookingsGroupLineDiscountComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-form-field", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, " % ");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](3, "mat-slide-toggle", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("change", function BookingEditBookingsGroupLineDiscountComponent_Template_mat_slide_toggle_change_3_listener($event) { return ctx.vm.type.change($event.checked); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](4, "\u20AC");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](5, "textarea", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](6, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](7, "mat-form-field", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](8, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("blur", function BookingEditBookingsGroupLineDiscountComponent_Template_input_blur_8_listener($event) { return ctx.vm.value.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](9, BookingEditBookingsGroupLineDiscountComponent_mat_error_9_Template, 2, 0, "mat-error", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("formControl", ctx.vm.type.formControl);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("formControl", ctx.vm.value.formControl);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", !ctx.vm.value.formControl.hasError("required"));
    } }, directives: [_angular_material_form_field__WEBPACK_IMPORTED_MODULE_6__.MatFormField, _angular_material_slide_toggle__WEBPACK_IMPORTED_MODULE_7__.MatSlideToggle, _angular_forms__WEBPACK_IMPORTED_MODULE_1__.NgControlStatus, _angular_forms__WEBPACK_IMPORTED_MODULE_1__.FormControlDirective, _angular_material_input__WEBPACK_IMPORTED_MODULE_8__.MatInput, _angular_forms__WEBPACK_IMPORTED_MODULE_1__.NumberValueAccessor, _angular_forms__WEBPACK_IMPORTED_MODULE_1__.DefaultValueAccessor, _angular_common__WEBPACK_IMPORTED_MODULE_9__.NgIf, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_6__.MatError], styles: ["[_nghost-%COMP%] {\n  display: block;\n  width: 100%;\n  height: 100%;\n  max-height: 50px;\n}\n[_nghost-%COMP%]   .cell[_ngcontent-%COMP%] {\n  display: inline-block;\n}\n[_nghost-%COMP%]   mat-form-field.invisible[_ngcontent-%COMP%]    .mat-form-field-underline {\n  display: none !important;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5ib29raW5ncy5ncm91cC5saW5lLmRpc2NvdW50LmNvbXBvbmVudC5zY3NzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0VBQ0UsY0FBQTtFQUNBLFdBQUE7RUFDQSxZQUFBO0VBQ0EsZ0JBQUE7QUFDRjtBQUNFO0VBQ0UscUJBQUE7QUFDSjtBQUdJO0VBQ0Usd0JBQUE7QUFETiIsImZpbGUiOiJib29raW5nLmVkaXQuYm9va2luZ3MuZ3JvdXAubGluZS5kaXNjb3VudC5jb21wb25lbnQuc2NzcyIsInNvdXJjZXNDb250ZW50IjpbIjpob3N0IHtcclxuICBkaXNwbGF5OiBibG9jaztcclxuICB3aWR0aDogMTAwJTtcclxuICBoZWlnaHQ6IDEwMCU7XHJcbiAgbWF4LWhlaWdodDogNTBweDsgXHJcblxyXG4gIC5jZWxsIHtcclxuICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcclxuICB9XHJcblxyXG4gIG1hdC1mb3JtLWZpZWxkLmludmlzaWJsZSB7XHJcbiAgICA6Om5nLWRlZXAubWF0LWZvcm0tZmllbGQtdW5kZXJsaW5lIHtcclxuICAgICAgZGlzcGxheTogbm9uZSAhaW1wb3J0YW50OyBcclxuICAgIH1cclxuICB9XHJcblxyXG5cclxufSJdfQ== */"] });


/***/ }),

/***/ 6377:
/*!*************************************************************************************************************************************************************!*\
  !*** ./src/app/in/bookings/edit/components/booking.edit.bookings/components/booking.edit.bookings.group.line/booking.edit.bookings.group.line.component.ts ***!
  \*************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditBookingsGroupLineComponent": () => (/* binding */ BookingEditBookingsGroupLineComponent)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var _angular_forms__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @angular/forms */ 3679);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! rxjs */ 8229);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! rxjs */ 9165);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! rxjs/operators */ 4395);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! rxjs/operators */ 8002);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! rxjs/operators */ 9773);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_dialog__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @angular/material/dialog */ 2238);
/* harmony import */ var _angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @angular/material/snack-bar */ 7001);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _angular_material_form_field__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @angular/material/form-field */ 8295);
/* harmony import */ var _angular_material_input__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @angular/material/input */ 3166);
/* harmony import */ var _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! @angular/material/autocomplete */ 1554);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_icon__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! @angular/material/icon */ 6627);
/* harmony import */ var _angular_material_core__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! @angular/material/core */ 7817);
/* harmony import */ var _booking_edit_bookings_group_line_discount_booking_edit_bookings_group_line_discount_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../booking.edit.bookings.group.line.discount/booking.edit.bookings.group.line.discount.component */ 6628);

















function BookingEditBookingsGroupLineComponent_button_4_Template(rf, ctx) { if (rf & 1) {
    const _r20 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "button", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_button_4_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r20); _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1); return _r0.identification.folded = !_r0.identification.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-icon", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "keyboard_arrow_up");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_button_5_Template(rf, ctx) { if (rf & 1) {
    const _r22 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "button", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_button_5_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r22); _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1); return _r0.identification.folded = !_r0.identification.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-icon", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "keyboard_arrow_right");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_mat_icon_13_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "mat-icon", 21);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1, "hotel");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_span_17_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1, "au logement");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_span_18_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1, "\u00E0 la personne");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_span_19_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1, "\u00E0 l'unit\u00E9");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_button_20_Template(rf, ctx) { if (rf & 1) {
    const _r24 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "button", 22);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_button_20_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r24); const ctx_r23 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](); return ctx_r23.vm.product.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_div_23_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "mat-option", 24);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const product_r28 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("value", product_r28);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate1"](" ", product_r28.name, " ");
} }
function BookingEditBookingsGroupLineComponent_div_23_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_div_23_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](1, BookingEditBookingsGroupLineComponent_div_23_mat_option_1_Template, 2, 2, "mat-option", 23);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](2, BookingEditBookingsGroupLineComponent_div_23_mat_option_2_Template, 3, 0, "mat-option", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r25 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngForOf", list_r25);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", list_r25.length == 0);
} }
function BookingEditBookingsGroupLineComponent_span_26_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r10 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate"](ctx_r10.vm.qty.value);
} }
function BookingEditBookingsGroupLineComponent_mat_form_field_27_mat_error_4_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "mat-error");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1, " Ne peut \u00EAtre vide. ");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_mat_form_field_27_Template(rf, ctx) { if (rf & 1) {
    const _r31 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "mat-form-field");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "Quantit\u00E9");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](3, "input", 25);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("blur", function BookingEditBookingsGroupLineComponent_mat_form_field_27_Template_input_blur_3_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r31); const ctx_r30 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](); return ctx_r30.vm.qty.change(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](4, BookingEditBookingsGroupLineComponent_mat_form_field_27_mat_error_4_Template, 2, 0, "mat-error", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r11 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("formControl", ctx_r11.vm.qty.formControl);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx_r11.vm.qty.formControl.hasError("required"));
} }
function BookingEditBookingsGroupLineComponent_span_29_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r12 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate"](ctx_r12.vm.free_qty.value);
} }
function BookingEditBookingsGroupLineComponent_span_31_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](2, "number");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r13 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate1"]("", _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind2"](2, 1, ctx_r13.vm.unit_price.value, "1.2-2"), " \u20AC");
} }
function BookingEditBookingsGroupLineComponent_span_33_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](2, "percent");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r14 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate"](_angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind1"](2, 1, ctx_r14.vm.discount.value));
} }
function BookingEditBookingsGroupLineComponent_span_35_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](2, "percent");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r15 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate"](_angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind1"](2, 1, ctx_r15.vm.vat.value));
} }
function BookingEditBookingsGroupLineComponent_span_37_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](2, "number");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r16 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate1"]("", _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind2"](2, 1, ctx_r16.vm.total_price.value, "1.2-2"), " \u20AC");
} }
function BookingEditBookingsGroupLineComponent_div_39_button_3_Template(rf, ctx) { if (rf & 1) {
    const _r36 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "button", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_div_39_button_3_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r36); _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1); return _r0.adapters.folded = !_r0.adapters.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-icon", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "keyboard_arrow_up");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_div_39_button_4_Template(rf, ctx) { if (rf & 1) {
    const _r38 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "button", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_div_39_button_4_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r38); _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1); return _r0.adapters.folded = !_r0.adapters.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-icon", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "keyboard_arrow_right");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_span_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](2, "number");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const adapter_r40 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]().$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate1"]("", _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind2"](2, 1, adapter_r40.value, "1.2-2"), " \u20AC");
} }
function BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_span_3_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](2, "percent");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const adapter_r40 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]().$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate"](_angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind1"](2, 1, adapter_r40.value));
} }
function BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_span_4_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const adapter_r40 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]().$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate"](adapter_r40.value);
} }
function BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](2, BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_span_2_Template, 3, 4, "span", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](3, BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_span_3_Template, 3, 3, "span", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](4, BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_span_4_Template, 2, 1, "span", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const adapter_r40 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtextInterpolate1"](" ", adapter_r40.discount_id.name, " : ");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", adapter_r40.type == "amount");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", adapter_r40.type == "percent");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", adapter_r40.type == "freebie");
} }
function BookingEditBookingsGroupLineComponent_div_39_div_9_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 29);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](2, BookingEditBookingsGroupLineComponent_div_39_div_9_div_2_Template, 5, 4, "div", 30);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r34 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngForOf", ctx_r34.adapters);
} }
function BookingEditBookingsGroupLineComponent_div_39_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 26);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](2, "div", 3);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](3, BookingEditBookingsGroupLineComponent_div_39_button_3_Template, 3, 0, "button", 4);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](4, BookingEditBookingsGroupLineComponent_div_39_button_4_Template, 3, 0, "button", 4);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](5, "div", 5);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](6, "div", 27);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](7, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](8, "D\u00E9tail du prix");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](9, BookingEditBookingsGroupLineComponent_div_39_div_9_Template, 3, 1, "div", 28);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r17 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵclassProp"]("hidden", !ctx_r17.ready);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !_r0.adapters.folded);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", _r0.adapters.folded);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](5);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !_r0.adapters.folded);
} }
function BookingEditBookingsGroupLineComponent_div_40_button_3_Template(rf, ctx) { if (rf & 1) {
    const _r52 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "button", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_div_40_button_3_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r52); _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1); return _r0.discounts.folded = !_r0.discounts.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-icon", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "keyboard_arrow_up");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_div_40_button_4_Template(rf, ctx) { if (rf & 1) {
    const _r54 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "button", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_div_40_button_4_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r54); _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1); return _r0.discounts.folded = !_r0.discounts.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "mat-icon", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](2, "keyboard_arrow_right");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupLineComponent_div_40_div_12_div_2_Template(rf, ctx) { if (rf & 1) {
    const _r59 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 34);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 35);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](2, "button", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_div_40_div_12_div_2_Template_button_click_2_listener() { const restoredCtx = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r59); const discount_r56 = restoredCtx.$implicit; const ctx_r58 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](3); return ctx_r58.vm.discounts.remove(discount_r56); });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](3, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](4, "delete");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](5, "booking-edit-bookings-group-line-discount", 36);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const index_r57 = ctx.index;
    const ctx_r55 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](5);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("discountInput", ctx_r55._discountOutput[index_r57])("discountOutput", ctx_r55._discountInput)("lineInput", ctx_r55.line)("lineOutput", ctx_r55.lineOutput);
} }
function BookingEditBookingsGroupLineComponent_div_40_div_12_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 29);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 32);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](2, BookingEditBookingsGroupLineComponent_div_40_div_12_div_2_Template, 6, 4, "div", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r50 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngForOf", ctx_r50.discounts);
} }
function BookingEditBookingsGroupLineComponent_div_40_Template(rf, ctx) { if (rf & 1) {
    const _r61 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](0, "div", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](1, "div", 26);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](2, "div", 3);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](3, BookingEditBookingsGroupLineComponent_div_40_button_3_Template, 3, 0, "button", 4);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](4, BookingEditBookingsGroupLineComponent_div_40_button_4_Template, 3, 0, "button", 4);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](5, "div", 5);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](6, "div", 27);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](7, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](8, "R\u00E9ductions ");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](9, "button", 31);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("click", function BookingEditBookingsGroupLineComponent_div_40_Template_button_click_9_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵrestoreView"](_r61); const ctx_r60 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1); ctx_r60.vm.discounts.add(); return _r0.discounts.folded = false; });
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](10, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](11, "add");
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](12, BookingEditBookingsGroupLineComponent_div_40_div_12_Template, 3, 1, "div", 28);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r18 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵnextContext"]();
    const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵclassProp"]("hidden", !ctx_r18.ready);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !_r0.discounts.folded);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", _r0.discounts.folded);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](8);
    _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !_r0.discounts.folded);
} }
const _c0 = function () { return { folded: true }; };
const _c1 = function (a0, a1, a2) { return { identification: a0, discounts: a1, adapters: a2 }; };
class BookingEditBookingsGroupLineComponent {
    constructor(api, auth, dialog, zone, snack) {
        this.api = api;
        this.auth = auth;
        this.dialog = dialog;
        this.zone = zone;
        this.snack = snack;
        // observable for updates from children components
        this._discountInput = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
        // array of observable for children components
        this._discountOutput = [];
        // fields for sub-items
        this.model_fields = {
            BookingPriceAdapter: ["id", "type", "value", "discount_id.name", "booking_line_id"]
        };
        this.ready = false;
        this.line = {};
        this.product = null;
        this.discounts = [];
        this.vm = {
            product: {
                name: '',
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_3__.Observable(),
                change: (event) => this.productChange(event),
                inputChange: (event) => this.productInputChange(event),
                focus: () => this.productFocus(),
                restore: () => this.productRestore(),
                reset: () => this.productReset(),
                display: (type) => this.productDisplay(type)
            },
            qty: {
                value: 0,
                formControl: new _angular_forms__WEBPACK_IMPORTED_MODULE_4__.FormControl('', _angular_forms__WEBPACK_IMPORTED_MODULE_4__.Validators.required),
                change: () => this.qtyChange()
            },
            free_qty: {
                value: 0.0
            },
            unit_price: {
                value: 0.0
            },
            discount: {
                value: 0.0
            },
            vat: {
                value: 0.0
            },
            total_price: {
                value: 0.0
            },
            discounts: {
                add: () => this.discountAdd(),
                remove: (discount) => this.discountRemove(discount)
            }
        };
    }
    ngOnInit() {
        this.lineInput.subscribe((line) => this.load(line));
        // listen to changes relayed by children component on the _bookingInput observable
        this._discountInput.subscribe(params => this.updateFromDiscount(params));
        /**
         * listen to the changes on FormControl objects
         */
        this.vm.product.filteredList = this.vm.product.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_5__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_6__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_7__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () { return this.filterProducts(name); })));
        this.vm.qty.formControl.valueChanges.subscribe((value) => {
            this.vm.qty.value = value;
        });
    }
    /**
     * Assign values from parent and load sub-objects required by the view.
     *
     * @param line
     */
    load(line) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            this.zone.run(() => {
                this.ready = false;
            });
            this.zone.run(() => (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
                try {
                    console.log("BookingEditBookingsGroupLineComponent: received changes from parent", line.id, line);
                    // update local group object
                    for (let field of Object.keys(line)) {
                        this.line[field] = line[field];
                    }
                    if (line.product_id) {
                        let data = yield this.api.read("lodging\\sale\\catalog\\Product", [line.product_id], ["id", "name", "sku"]);
                        if (data && data.length) {
                            let product = data[0];
                            this.product = product;
                            this.vm.product.name = product.name + ' (' + product.sku + ')';
                        }
                    }
                    // #todo - obtenir les réductions automatiques assignées au goupe et les afficher pour les produits pour lesquels elles sont d'application
                    // #todo - obtenir les réductions manuelles la ligne : dans un sous-component
                    if (line.hasOwnProperty('price_id')) {
                        let data = yield this.api.read("sale\\price\\Price", [line.price_id], ["id", "price"]);
                        if (data && data.length) {
                            let price = data[0];
                            this.price = price;
                            // #memo : price is a computed field set server-side, according to price adapters            
                            this.vm.unit_price.value = price.price;
                        }
                        else {
                            this.price = null;
                            this.vm.unit_price.value = 0;
                            this.vm.vat.value = 0;
                        }
                    }
                    if (line.hasOwnProperty('unit_price')) {
                        this.vm.unit_price.value = line.unit_price;
                    }
                    if (line.hasOwnProperty('vat_rate')) {
                        this.vm.vat.value = line.vat_rate;
                    }
                    if (line.hasOwnProperty('price')) {
                        this.vm.total_price.value = line.price;
                    }
                    if (line.hasOwnProperty('qty')) {
                        this.vm.qty.value = line.qty;
                        this.vm.qty.formControl.setValue(line.qty);
                    }
                    if (line.manual_discounts_ids) {
                        let data = yield this.loadManualDiscounts(line.manual_discounts_ids, this.model_fields['BookingPriceAdapter']);
                        if (data) {
                            for (let [index, discount] of data.entries()) {
                                // add new discounts (indexes from this.discount and this._lineOutput are synced)
                                if (index >= this.discounts.length) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
                                    this._discountOutput.push(item);
                                    item.next(discount);
                                    this.discounts.push(discount);
                                }
                                // if discount differ, overwrite previsously assigned discount
                                else if (JSON.stringify(this.discounts[index]) != JSON.stringify(discount)) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
                                    this._discountOutput[index] = item;
                                    this.discounts[index] = discount;
                                    item.next(discount);
                                }
                            }
                            // remove remaining discounts, if any
                            if (this.discounts.length > data.length) {
                                this.discounts.splice(data.length);
                                this._discountOutput.splice(data.length);
                            }
                        }
                        let discount = 0.0;
                        for (let item of this.discounts) {
                            if (item['type'] == 'percent') {
                                discount += item['value'];
                            }
                        }
                        this.vm.discount.value = discount;
                    }
                    // allways load price adapters
                    if (line.auto_discounts_ids) {
                        let free_qty = 0;
                        const adapters = yield this.loadAutoDiscounts(line.auto_discounts_ids, this.model_fields['BookingPriceAdapter']);
                        this.adapters = adapters;
                        for (let adapter of this.adapters) {
                            if (adapter['type'] == 'freebie') {
                                free_qty += adapter['value'];
                            }
                        }
                        this.vm.free_qty.value = free_qty;
                    }
                }
                catch (response) {
                    console.warn(response);
                }
                this.ready = true;
            }));
        });
    }
    /**
     * Handle update events received from BookingLine children.
     *
     */
    updateFromDiscount(discount) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            console.log("BookingEditBookingsGroupLineComponent: received changes from child", discount);
            console.log(this.discounts);
            try {
                let has_change = false;
                let index = this.discounts.findIndex((element) => element.id == discount.id);
                let t_line = this.discounts.find((element) => element.id == discount.id);
                if (discount.hasOwnProperty('value') && discount.value != t_line.value) {
                    yield this.updateDiscount(discount);
                    has_change = true;
                }
                if (discount.hasOwnProperty('type') && discount.type != t_line.type) {
                    yield this.updateDiscount(discount);
                    has_change = true;
                }
                if (has_change) {
                    this.lineOutput.next({ id: this.line.id, price_adapters_ids: true });
                }
            }
            catch (error) {
                console.warn('some changes could not be stored', error);
                this.snack.open("Erreur - certains changements n'ont pas pu être enregistrés.");
            }
        });
    }
    productInputChange(event) {
        this.vm.product.inputClue.next(event.target.value);
    }
    productFocus() {
        this.vm.product.inputClue.next("");
    }
    productDisplay(product) {
        return product ? product.name + ' (' + product.sku + ')' : '';
    }
    productReset() {
        setTimeout(() => {
            this.vm.product.name = '';
        }, 100);
    }
    productRestore() {
        if (this.product) {
            this.vm.product.name = this.product.name + ' (' + this.product.sku + ')';
        }
        else {
            this.vm.product.name = '';
        }
    }
    productChange(event) {
        console.log('BookingEditCustomerComponent::productChange', event);
        // from mat-autocomplete
        if (event && event.option && event.option.value) {
            let product = event.option.value;
            this.product = product;
            this.vm.product.name = product.name + ' (' + product.sku + ')';
            // relay change to parent component
            this.lineOutput.next({ id: this.line.id, product_id: product.id, refresh: { self: ['price'] } });
        }
    }
    qtyChange() {
        // relay change to parent component
        this.lineOutput.next({ id: this.line.id, qty: this.vm.qty.value, refresh: { self: ['price'] } });
    }
    filterProducts(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            /*
            #todo - limit products to the ones available for currently selected center
            $families_ids = center.product_families_ids
            $products = sale\catalog\Product::search(['family_id', 'in', $families_ids])
            */
            let filtered = [];
            try {
                let data = yield this.api.collect("lodging\\sale\\catalog\\Product", [["name", "ilike", '%' + name + '%']], ["id", "name", "sku"], 'name', 'asc', 0, 25);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
    loadAutoDiscounts(ids, fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            let data = yield this.api.read("lodging\\sale\\booking\\BookingPriceAdapter", ids, fields);
            return data;
        });
    }
    loadManualDiscounts(ids, fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            let data = yield this.api.read("lodging\\sale\\booking\\BookingPriceAdapter", ids, fields);
            return data;
        });
    }
    discountAdd() {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            try {
                const adapter = yield this.api.create("lodging\\sale\\booking\\BookingPriceAdapter", {
                    booking_id: this.line.booking_id,
                    booking_line_group_id: this.line.booking_line_group_id,
                    booking_line_id: this.line.id
                });
                // emit change to parent
                // this.lineOutput.next({id: this.line.id, price_adapters_ids: [adapter.id]});
                let discount = { id: adapter.id, type: 'percent', value: 0 };
                let item = new rxjs__WEBPACK_IMPORTED_MODULE_2__.ReplaySubject(1);
                this._discountOutput.push(item);
                item.next(discount);
                this.discounts.push(discount);
            }
            catch (error) {
                console.log(error);
            }
        });
    }
    discountRemove(discount) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            // await this.api.remove("lodging\\sale\\booking\\BookingPriceAdapter", [discount.id], true);
            yield this.api.update("lodging\\sale\\booking\\BookingLine", [this.line.id], { price_adapters_ids: [-discount.id] }, discount);
            this.lineOutput.next({ id: this.line.id, price_adapters_ids: true });
        });
    }
    updateDiscount(discount) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_8__.__awaiter)(this, void 0, void 0, function* () {
            yield this.api.update("lodging\\sale\\booking\\BookingPriceAdapter", [discount.id], discount);
        });
    }
}
BookingEditBookingsGroupLineComponent.ɵfac = function BookingEditBookingsGroupLineComponent_Factory(t) { return new (t || BookingEditBookingsGroupLineComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_9__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_9__.AuthService), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_10__.MatDialog), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_1__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdirectiveInject"](_angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_11__.MatSnackBar)); };
BookingEditBookingsGroupLineComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵdefineComponent"]({ type: BookingEditBookingsGroupLineComponent, selectors: [["booking-edit-bookings-group-line"]], inputs: { groupInput: "groupInput", lineOutput: "lineOutput", lineInput: "lineInput" }, decls: 41, vars: 37, consts: [[3, "var"], ["vProduct", "var"], [1, "part"], [1, "part-toggle"], ["mat-icon-button", "", 3, "click", 4, "ngIf"], [1, "part-container"], [1, "row", "row-first"], [1, "cell"], ["matInput", "", "type", "text", "placeholder", "Commencez \u00E0 taper le nom", 3, "matAutocomplete", "value", "keyup", "focus", "blur"], ["matSuffix", "", "style", "vertical-align: sub;", 4, "ngIf"], [3, "align"], [4, "ngIf"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click", 4, "ngIf"], [3, "displayWith", "optionSelected"], ["productAutocomplete", "matAutocomplete"], [1, "cell", "cell-right"], [1, "cell", "cell-text", "cell-right"], [1, "cell", "cell-actions"], ["class", "row row-first", 4, "ngIf"], ["mat-icon-button", "", 3, "click"], [2, "font-size", "15px"], ["matSuffix", "", 2, "vertical-align", "sub"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click"], [3, "value", 4, "ngFor", "ngForOf"], [3, "value"], ["type", "number", "matInput", "", 3, "formControl", "blur"], [1, "part", "cell"], [1, "row", "row-title", "row-thin"], ["class", "row row-thin", 4, "ngIf"], [1, "row", "row-thin"], [4, "ngFor", "ngForOf"], ["mat-mini-fab", "", "color", "primary", 2, "transform", "scale(0.65)", 3, "click"], [1, "discounts-list"], ["class", "discount-item", 4, "ngFor", "ngForOf"], [1, "discount-item"], [1, "discount-remove"], [3, "discountInput", "discountOutput", "lineInput", "lineOutput"]], template: function BookingEditBookingsGroupLineComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](0, "div", 0, 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](2, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](3, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](4, BookingEditBookingsGroupLineComponent_button_4_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](5, BookingEditBookingsGroupLineComponent_button_5_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](6, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](7, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](8, "div", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](9, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](10, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](11, "Produit");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](12, "input", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("keyup", function BookingEditBookingsGroupLineComponent_Template_input_keyup_12_listener($event) { return ctx.vm.product.inputChange($event); })("focus", function BookingEditBookingsGroupLineComponent_Template_input_focus_12_listener() { return ctx.vm.product.focus(); })("blur", function BookingEditBookingsGroupLineComponent_Template_input_blur_12_listener() { return ctx.vm.product.restore(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](13, BookingEditBookingsGroupLineComponent_mat_icon_13_Template, 2, 0, "mat-icon", 9);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](14, "mat-hint");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtext"](15, "Hint");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](16, "mat-hint", 10);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](17, BookingEditBookingsGroupLineComponent_span_17_Template, 2, 0, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](18, BookingEditBookingsGroupLineComponent_span_18_Template, 2, 0, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](19, BookingEditBookingsGroupLineComponent_span_19_Template, 2, 0, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](20, BookingEditBookingsGroupLineComponent_button_20_Template, 3, 0, "button", 12);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](21, "mat-autocomplete", 13, 14);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵlistener"]("optionSelected", function BookingEditBookingsGroupLineComponent_Template_mat_autocomplete_optionSelected_21_listener($event) { return ctx.vm.product.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](23, BookingEditBookingsGroupLineComponent_div_23_Template, 3, 2, "div", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipe"](24, "async");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](25, "div", 15);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](26, BookingEditBookingsGroupLineComponent_span_26_Template, 2, 1, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](27, BookingEditBookingsGroupLineComponent_mat_form_field_27_Template, 5, 2, "mat-form-field", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](28, "div", 16);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](29, BookingEditBookingsGroupLineComponent_span_29_Template, 2, 1, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](30, "div", 16);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](31, BookingEditBookingsGroupLineComponent_span_31_Template, 3, 4, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](32, "div", 16);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](33, BookingEditBookingsGroupLineComponent_span_33_Template, 3, 3, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](34, "div", 16);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](35, BookingEditBookingsGroupLineComponent_span_35_Template, 3, 3, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementStart"](36, "div", 16);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](37, BookingEditBookingsGroupLineComponent_span_37_Template, 3, 4, "span", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelement"](38, "div", 17);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](39, BookingEditBookingsGroupLineComponent_div_39_Template, 10, 5, "div", 18);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵtemplate"](40, BookingEditBookingsGroupLineComponent_div_40_Template, 13, 5, "div", 18);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵelementEnd"]();
    } if (rf & 2) {
        const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](1);
        const _r8 = _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵreference"](22);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("var", _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpureFunction3"](33, _c1, _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpureFunction0"](30, _c0), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpureFunction0"](31, _c0), _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpureFunction0"](32, _c0)));
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵclassProp"]("hidden", !ctx.ready);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵclassProp"]("hidden", ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !_r0.identification.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", _r0.identification.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](7);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("matAutocomplete", _r8)("value", ctx.vm.product.name);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.line.qty_accounting_method == "accomodation");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](3);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("align", "end");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.line.qty_accounting_method == "accomodation");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.line.qty_accounting_method == "person");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.line.qty_accounting_method == "unit");
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.vm.product.name.length);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("displayWith", ctx.vm.product.display);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵpipeBind1"](24, 28, ctx.vm.product.filteredList));
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵclassProp"]("cell-text", ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !_r0.identification.folded && !ctx.groupInput.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_1__["ɵɵproperty"]("ngIf", !_r0.identification.folded && !ctx.groupInput.is_locked);
    } }, directives: [sb_shared_lib__WEBPACK_IMPORTED_MODULE_9__.VarDirective, _angular_common__WEBPACK_IMPORTED_MODULE_12__.NgIf, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_13__.MatFormField, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_13__.MatLabel, _angular_material_input__WEBPACK_IMPORTED_MODULE_14__.MatInput, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_15__.MatAutocompleteTrigger, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_13__.MatHint, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_15__.MatAutocomplete, _angular_material_button__WEBPACK_IMPORTED_MODULE_16__.MatButton, _angular_material_icon__WEBPACK_IMPORTED_MODULE_17__.MatIcon, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_13__.MatSuffix, _angular_common__WEBPACK_IMPORTED_MODULE_12__.NgForOf, _angular_material_core__WEBPACK_IMPORTED_MODULE_18__.MatOption, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.NumberValueAccessor, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.DefaultValueAccessor, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.NgControlStatus, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.FormControlDirective, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_13__.MatError, _booking_edit_bookings_group_line_discount_booking_edit_bookings_group_line_discount_component__WEBPACK_IMPORTED_MODULE_0__.BookingEditBookingsGroupLineDiscountComponent], pipes: [_angular_common__WEBPACK_IMPORTED_MODULE_12__.AsyncPipe, _angular_common__WEBPACK_IMPORTED_MODULE_12__.DecimalPipe, _angular_common__WEBPACK_IMPORTED_MODULE_12__.PercentPipe], styles: ["[_nghost-%COMP%] {\n  display: block;\n  width: 100%;\n  height: 100%;\n}\n[_nghost-%COMP%]   .hidden[_ngcontent-%COMP%] {\n  visibility: hidden;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%] {\n  display: flex;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-toggle[_ngcontent-%COMP%] {\n  flex: 0 1;\n  display: inline-block;\n  vertical-align: top;\n  padding: 5px 0px 0px 20px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%] {\n  flex: 1;\n  display: inline-block;\n  width: 100%;\n  min-height: 45px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%] {\n  width: 100%;\n  display: flex;\n  padding: 5px 0;\n  flex: 1;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .cell[_ngcontent-%COMP%] {\n  position: relative;\n  padding: 0 10px;\n  margin-right: 15px;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .cell.cell-text[_ngcontent-%COMP%] {\n  line-height: 45px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .cell.cell-right[_ngcontent-%COMP%] {\n  text-align: right;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .cell.cell-actions[_ngcontent-%COMP%] {\n  max-width: 35px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .cell[_ngcontent-%COMP%]:first-child {\n  flex: 0 0 calc((100% + 60px)*0.40 - 60px);\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .cell[_ngcontent-%COMP%]:not(:first-child) {\n  flex: 1 0 calc((100% + 60px)*0.08);\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row.row-first[_ngcontent-%COMP%]     .mat-form-field-wrapper {\n  padding-bottom: 15px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row.row-title[_ngcontent-%COMP%] {\n  height: 45px;\n  line-height: 45px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row.row-thin[_ngcontent-%COMP%] {\n  padding: 0;\n}\n[_nghost-%COMP%]   mat-form-field[_ngcontent-%COMP%] {\n  width: 100%;\n  padding: 0px 12px;\n}\n[_nghost-%COMP%]   mat-form-field.header[_ngcontent-%COMP%] {\n  margin-top: -5px;\n}\n[_nghost-%COMP%]   mat-form-field.header[_ngcontent-%COMP%]   input[_ngcontent-%COMP%] {\n  font-size: 22px;\n}\n[_nghost-%COMP%]   mat-form-field.invisible[_ngcontent-%COMP%]    .mat-form-field-underline {\n  display: none !important;\n}\n[_nghost-%COMP%]   p[_ngcontent-%COMP%] {\n  font-size: 14px;\n  padding: 0px 12px;\n}\n[_nghost-%COMP%]   .discounts-list[_ngcontent-%COMP%] {\n  position: relative;\n  padding: 0 12px 0 0;\n  width: 100%;\n}\n[_nghost-%COMP%]   .discounts-list[_ngcontent-%COMP%]   .discount-item[_ngcontent-%COMP%] {\n  position: relative;\n  background-color: white;\n}\n[_nghost-%COMP%]   .discounts-list[_ngcontent-%COMP%]   .discount-item[_ngcontent-%COMP%]   .discount-remove[_ngcontent-%COMP%] {\n  position: absolute;\n  right: -5px;\n  top: 10px;\n  z-index: 2;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5ib29raW5ncy5ncm91cC5saW5lLmNvbXBvbmVudC5zY3NzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0VBQ0UsY0FBQTtFQUNBLFdBQUE7RUFDQSxZQUFBO0FBQ0Y7QUFDRTtFQUNFLGtCQUFBO0FBQ0o7QUFFRTtFQUNFLGFBQUE7QUFBSjtBQUVJO0VBQ0UsU0FBQTtFQUNBLHFCQUFBO0VBQ0EsbUJBQUE7RUFDQSx5QkFBQTtBQUFOO0FBR0k7RUFDRSxPQUFBO0VBQ0EscUJBQUE7RUFDQSxXQUFBO0VBQ0EsZ0JBQUE7QUFETjtBQUdNO0VBQ0UsV0FBQTtFQUNBLGFBQUE7RUFDQSxjQUFBO0VBQ0EsT0FBQTtBQURSO0FBSVE7RUFDRSxrQkFBQTtFQUNBLGVBQUE7RUFDQSxrQkFBQTtFQUNBLG1CQUFBO0VBQ0EsdUJBQUE7RUFDQSxnQkFBQTtBQUZWO0FBTVE7RUFDRSxpQkFBQTtBQUpWO0FBT1E7RUFDRSxpQkFBQTtBQUxWO0FBUVE7RUFDRSxlQUFBO0FBTlY7QUFTUTtFQUNFLHlDQUFBO0FBUFY7QUFVUTtFQUNFLGtDQUFBO0FBUlY7QUFlUTtFQUNFLG9CQUFBO0FBYlY7QUFpQk07RUFDRSxZQUFBO0VBQ0EsaUJBQUE7QUFmUjtBQWtCTTtFQUNFLFVBQUE7QUFoQlI7QUFxQkU7RUFDRSxXQUFBO0VBQ0EsaUJBQUE7QUFuQko7QUF1QkU7RUFDRSxnQkFBQTtBQXJCSjtBQXVCSTtFQUNFLGVBQUE7QUFyQk47QUEwQkk7RUFDRSx3QkFBQTtBQXhCTjtBQTRCRTtFQUNFLGVBQUE7RUFDQSxpQkFBQTtBQTFCSjtBQTZCRTtFQUNFLGtCQUFBO0VBQ0EsbUJBQUE7RUFDQSxXQUFBO0FBM0JKO0FBNkJJO0VBQ0Usa0JBQUE7RUFDQSx1QkFBQTtBQTNCTjtBQTZCTTtFQUNFLGtCQUFBO0VBQ0EsV0FBQTtFQUNBLFNBQUE7RUFDQSxVQUFBO0FBM0JSIiwiZmlsZSI6ImJvb2tpbmcuZWRpdC5ib29raW5ncy5ncm91cC5saW5lLmNvbXBvbmVudC5zY3NzIiwic291cmNlc0NvbnRlbnQiOlsiOmhvc3Qge1xyXG4gIGRpc3BsYXk6IGJsb2NrO1xyXG4gIHdpZHRoOiAxMDAlO1xyXG4gIGhlaWdodDogMTAwJTtcclxuXHJcbiAgLmhpZGRlbiB7XHJcbiAgICB2aXNpYmlsaXR5OiBoaWRkZW47XHJcbiAgfVxyXG4gIFxyXG4gIC5wYXJ0IHtcclxuICAgIGRpc3BsYXk6IGZsZXg7XHJcblxyXG4gICAgLnBhcnQtdG9nZ2xlIHtcclxuICAgICAgZmxleDogMCAxO1xyXG4gICAgICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7XHJcbiAgICAgIHZlcnRpY2FsLWFsaWduOiB0b3A7XHJcbiAgICAgIHBhZGRpbmc6IDVweCAwcHggMHB4IDIwcHg7ICAgICAgXHJcbiAgICB9XHJcblxyXG4gICAgLnBhcnQtY29udGFpbmVyIHtcclxuICAgICAgZmxleDogMTtcclxuICAgICAgZGlzcGxheTogaW5saW5lLWJsb2NrO1xyXG4gICAgICB3aWR0aDogMTAwJTtcclxuICAgICAgbWluLWhlaWdodDogNDVweDtcclxuXHJcbiAgICAgIC5yb3cge1xyXG4gICAgICAgIHdpZHRoOiAxMDAlO1xyXG4gICAgICAgIGRpc3BsYXk6IGZsZXg7XHJcbiAgICAgICAgcGFkZGluZzogNXB4IDA7XHJcbiAgICAgICAgZmxleDogMTtcclxuICAgICAgXHJcbiAgICBcclxuICAgICAgICAuY2VsbCB7XHJcbiAgICAgICAgICBwb3NpdGlvbjogcmVsYXRpdmU7XHJcbiAgICAgICAgICBwYWRkaW5nOiAwIDEwcHg7XHJcbiAgICAgICAgICBtYXJnaW4tcmlnaHQ6IDE1cHg7XHJcbiAgICAgICAgICB3aGl0ZS1zcGFjZTogbm93cmFwO1xyXG4gICAgICAgICAgdGV4dC1vdmVyZmxvdzogZWxsaXBzaXM7XHJcbiAgICAgICAgICBvdmVyZmxvdzogaGlkZGVuO1xyXG5cclxuICAgICAgICB9ICAgIFxyXG5cclxuICAgICAgICAuY2VsbC5jZWxsLXRleHQge1xyXG4gICAgICAgICAgbGluZS1oZWlnaHQ6IDQ1cHg7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAuY2VsbC5jZWxsLXJpZ2h0IHtcclxuICAgICAgICAgIHRleHQtYWxpZ246IHJpZ2h0OyAgICAgICAgICBcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIC5jZWxsLmNlbGwtYWN0aW9ucyB7XHJcbiAgICAgICAgICBtYXgtd2lkdGg6IDM1cHg7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAuY2VsbDpmaXJzdC1jaGlsZCAge1xyXG4gICAgICAgICAgZmxleDogMCAwIGNhbGMoKDEwMCUgKyA2MHB4KSowLjQwIC0gNjBweCk7XHJcbiAgICAgICAgfSAgICBcclxuXHJcbiAgICAgICAgLmNlbGw6bm90KDpmaXJzdC1jaGlsZCkgIHtcclxuICAgICAgICAgIGZsZXg6IDEgMCBjYWxjKCgxMDAlICsgNjBweCkqMC4wOCk7XHJcbiAgICAgICAgfSAgICBcclxuICAgICAgfVxyXG5cclxuICAgICAgLnJvdy5yb3ctZmlyc3Qge1xyXG4gICAgICAgIC8vIG1heC1oZWlnaHQ6IDU1cHg7XHJcbiAgICAgIFxyXG4gICAgICAgIDo6bmctZGVlcCAubWF0LWZvcm0tZmllbGQtd3JhcHBlciB7XHJcbiAgICAgICAgICBwYWRkaW5nLWJvdHRvbTogMTVweFxyXG4gICAgICAgIH1cclxuICAgICAgfVxyXG5cclxuICAgICAgLnJvdy5yb3ctdGl0bGUgeyBcclxuICAgICAgICBoZWlnaHQ6IDQ1cHg7XHJcbiAgICAgICAgbGluZS1oZWlnaHQ6IDQ1cHg7XHJcbiAgICAgIH1cclxuXHJcbiAgICAgIC5yb3cucm93LXRoaW4ge1xyXG4gICAgICAgIHBhZGRpbmc6IDA7XHJcbiAgICAgIH1cclxuICAgIH1cclxuICB9XHJcblxyXG4gIG1hdC1mb3JtLWZpZWxkIHtcclxuICAgIHdpZHRoOiAxMDAlO1xyXG4gICAgcGFkZGluZzogMHB4IDEycHg7XHJcblxyXG4gIH1cclxuXHJcbiAgbWF0LWZvcm0tZmllbGQuaGVhZGVyIHtcclxuICAgIG1hcmdpbi10b3A6IC01cHg7XHJcbiAgXHJcbiAgICBpbnB1dCB7XHJcbiAgICAgIGZvbnQtc2l6ZTogMjJweDtcclxuICAgIH1cclxuICB9XHJcblxyXG4gIG1hdC1mb3JtLWZpZWxkLmludmlzaWJsZSB7XHJcbiAgICA6Om5nLWRlZXAubWF0LWZvcm0tZmllbGQtdW5kZXJsaW5lIHtcclxuICAgICAgZGlzcGxheTogbm9uZSAhaW1wb3J0YW50OyBcclxuICAgIH1cclxuICB9XHJcblxyXG4gIHAge1xyXG4gICAgZm9udC1zaXplOiAxNHB4O1xyXG4gICAgcGFkZGluZzogMHB4IDEycHg7XHJcbiAgfVxyXG4gIFxyXG4gIC5kaXNjb3VudHMtbGlzdCB7XHJcbiAgICBwb3NpdGlvbjogcmVsYXRpdmU7XHJcbiAgICBwYWRkaW5nOiAwIDEycHggMCAwO1xyXG4gICAgd2lkdGg6IDEwMCU7XHJcblxyXG4gICAgLmRpc2NvdW50LWl0ZW0ge1xyXG4gICAgICBwb3NpdGlvbjogcmVsYXRpdmU7XHJcbiAgICAgIGJhY2tncm91bmQtY29sb3I6IHdoaXRlO1xyXG4gICAgXHJcbiAgICAgIC5kaXNjb3VudC1yZW1vdmUge1xyXG4gICAgICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTtcclxuICAgICAgICByaWdodDogLTVweDtcclxuICAgICAgICB0b3A6IDEwcHg7XHJcbiAgICAgICAgei1pbmRleDogMjtcclxuICAgICAgfVxyXG4gICAgXHJcbiAgICB9ICAgIFxyXG4gICAgXHJcbiAgfVxyXG59Il19 */"] });


/***/ }),

/***/ 6340:
/*!***************************************************************************************************************************************************!*\
  !*** ./src/app/in/bookings/edit/components/booking.edit.bookings/components/booking.edit.bookings.group/booking.edit.bookings.group.component.ts ***!
  \***************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditBookingsGroupComponent": () => (/* binding */ BookingEditBookingsGroupComponent)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var _angular_forms__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @angular/forms */ 3679);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! rxjs */ 8229);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! rxjs */ 9165);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! rxjs/operators */ 4395);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! rxjs/operators */ 8002);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! rxjs/operators */ 9773);
/* harmony import */ var _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @angular/cdk/drag-drop */ 7310);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_dialog__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @angular/material/dialog */ 2238);
/* harmony import */ var _angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @angular/material/snack-bar */ 7001);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_icon__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! @angular/material/icon */ 6627);
/* harmony import */ var _angular_material_form_field__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! @angular/material/form-field */ 8295);
/* harmony import */ var _angular_material_input__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! @angular/material/input */ 3166);
/* harmony import */ var _angular_material_datepicker__WEBPACK_IMPORTED_MODULE_19__ = __webpack_require__(/*! @angular/material/datepicker */ 3220);
/* harmony import */ var _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_20__ = __webpack_require__(/*! @angular/material/autocomplete */ 1554);
/* harmony import */ var _angular_material_select__WEBPACK_IMPORTED_MODULE_21__ = __webpack_require__(/*! @angular/material/select */ 7441);
/* harmony import */ var _angular_material_core__WEBPACK_IMPORTED_MODULE_22__ = __webpack_require__(/*! @angular/material/core */ 7817);
/* harmony import */ var _angular_material_slide_toggle__WEBPACK_IMPORTED_MODULE_23__ = __webpack_require__(/*! @angular/material/slide-toggle */ 5396);
/* harmony import */ var _booking_edit_bookings_group_line_booking_edit_bookings_group_line_component__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../booking.edit.bookings.group.line/booking.edit.bookings.group.line.component */ 6377);
/* harmony import */ var _booking_edit_bookings_group_accomodation_booking_edit_bookings_group_accomodation_component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../booking.edit.bookings.group.accomodation/booking.edit.bookings.group.accomodation.component */ 5795);























function BookingEditBookingsGroupComponent_button_5_Template(rf, ctx) { if (rf & 1) {
    const _r14 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_button_5_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r14); _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](1); return _r0.identification.folded = !_r0.identification.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "keyboard_arrow_up");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_button_6_Template(rf, ctx) { if (rf & 1) {
    const _r16 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_button_6_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r16); _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](1); return _r0.identification.folded = !_r0.identification.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "keyboard_arrow_right");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_7_mat_error_9_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "mat-error");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](1, " Ne peut \u00EAtre vide. ");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_7_Template(rf, ctx) { if (rf & 1) {
    const _r19 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "div", 14);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](3, "mat-form-field", 15);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](4, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](5, "Intitul\u00E9 du s\u00E9jour");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](6, "input", 16);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("blur", function BookingEditBookingsGroupComponent_div_7_Template_input_blur_6_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r19); const ctx_r18 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r18.vm.name.change(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](7, "mat-hint");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](8, "Libell\u00E9 du regroupement.");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](9, BookingEditBookingsGroupComponent_div_7_mat_error_9_Template, 2, 0, "mat-error", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r3 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](6);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("formControl", ctx_r3.vm.name.formControl);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !ctx_r3.vm.name.formControl.hasError("required"));
} }
function BookingEditBookingsGroupComponent_div_8_mat_error_9_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "mat-error");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](1, " Ne peut \u00EAtre vide. ");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_8_mat_error_30_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "mat-error");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](1, " Doit \u00EAtre sup\u00E9rieur \u00E0 0. ");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_8_button_37_Template(rf, ctx) { if (rf & 1) {
    const _r29 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 44);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_div_8_button_37_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r29); const ctx_r28 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2); return ctx_r28.vm.rate_class.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_8_div_40_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "mat-option", 46);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const rate_class_r33 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("value", rate_class_r33);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtextInterpolate1"](" ", rate_class_r33.name, " ");
} }
function BookingEditBookingsGroupComponent_div_8_div_40_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_8_div_40_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](1, BookingEditBookingsGroupComponent_div_8_div_40_mat_option_1_Template, 2, 2, "mat-option", 45);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](2, BookingEditBookingsGroupComponent_div_8_div_40_mat_option_2_Template, 3, 0, "mat-option", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r30 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngForOf", list_r30);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", list_r30.length == 0);
} }
function BookingEditBookingsGroupComponent_div_8_div_60_button_5_Template(rf, ctx) { if (rf & 1) {
    const _r38 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 44);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_div_8_div_60_button_5_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r38); const ctx_r37 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](3); return ctx_r37.vm.pack.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_8_div_60_div_8_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "mat-option", 46);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const pack_r42 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("value", pack_r42);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtextInterpolate1"](" ", pack_r42.name, " ");
} }
function BookingEditBookingsGroupComponent_div_8_div_60_div_8_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_8_div_60_div_8_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](1, BookingEditBookingsGroupComponent_div_8_div_60_div_8_mat_option_1_Template, 2, 2, "mat-option", 45);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](2, BookingEditBookingsGroupComponent_div_8_div_60_div_8_mat_option_2_Template, 3, 0, "mat-option", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r39 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngForOf", list_r39);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", list_r39.length == 0);
} }
function BookingEditBookingsGroupComponent_div_8_div_60_Template(rf, ctx) { if (rf & 1) {
    const _r44 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 47);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-form-field");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](3, "Pack de r\u00E9f\u00E9rence");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](4, "input", 29);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("keyup", function BookingEditBookingsGroupComponent_div_8_div_60_Template_input_keyup_4_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r44); const ctx_r43 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2); return ctx_r43.vm.pack.inputChange($event); })("focus", function BookingEditBookingsGroupComponent_div_8_div_60_Template_input_focus_4_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r44); const ctx_r45 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2); return ctx_r45.vm.pack.focus(); })("blur", function BookingEditBookingsGroupComponent_div_8_div_60_Template_input_blur_4_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r44); const ctx_r46 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2); return ctx_r46.vm.pack.restore(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](5, BookingEditBookingsGroupComponent_div_8_div_60_button_5_Template, 3, 0, "button", 30);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](6, "mat-autocomplete", 31, 48);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("optionSelected", function BookingEditBookingsGroupComponent_div_8_div_60_Template_mat_autocomplete_optionSelected_6_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r44); const ctx_r47 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2); return ctx_r47.vm.pack.change("name", $event.option.value); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](8, BookingEditBookingsGroupComponent_div_8_div_60_div_8_Template, 3, 2, "div", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpipe"](9, "async");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](10, "mat-hint", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](11, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](12, "Type de s\u00E9jour de la r\u00E9servation");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const _r35 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](7);
    const ctx_r26 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](4);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("matAutocomplete", _r35)("value", ctx_r26.vm.pack.name);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", ctx_r26.vm.pack.name.length);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("displayWith", ctx_r26.vm.pack.display);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpipeBind1"](9, 6, ctx_r26.vm.pack.filteredList));
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("align", "start");
} }
function BookingEditBookingsGroupComponent_div_8_div_61_Template(rf, ctx) { if (rf & 1) {
    const _r49 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 49);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-form-field", 39);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "mat-slide-toggle", 50);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("change", function BookingEditBookingsGroupComponent_div_8_div_61_Template_mat_slide_toggle_change_2_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r49); const ctx_r48 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2); return ctx_r48.vm.pack.change("is_locked", $event.checked); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](3, "Liste des produits verrouill\u00E9e");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelement"](4, "textarea", 41);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r27 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngModel", ctx_r27.vm.pack.is_locked);
} }
function BookingEditBookingsGroupComponent_div_8_Template(rf, ctx) { if (rf & 1) {
    const _r51 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "div", 14);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](3, "mat-form-field", 15);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](4, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](5, "Intitul\u00E9 du s\u00E9jour");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](6, "input", 16);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("blur", function BookingEditBookingsGroupComponent_div_8_Template_input_blur_6_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r50 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r50.vm.name.change(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](7, "mat-hint");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](8, "Libell\u00E9 du regroupement.");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](9, BookingEditBookingsGroupComponent_div_8_mat_error_9_Template, 2, 0, "mat-error", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](10, "div", 18);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](11, "mat-form-field");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](12, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](13, "Dates du s\u00E9jour");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](14, "mat-date-range-input", 19);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](15, "input", 20);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("blur", function BookingEditBookingsGroupComponent_div_8_Template_input_blur_15_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r52 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r52.vm.daterange.change(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](16, "input", 21);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("blur", function BookingEditBookingsGroupComponent_div_8_Template_input_blur_16_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r53 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r53.vm.daterange.change(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelement"](17, "mat-datepicker-toggle", 22);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](18, "mat-date-range-picker", 23, 24);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("closed", function BookingEditBookingsGroupComponent_div_8_Template_mat_date_range_picker_closed_18_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r54 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r54.vm.daterange.change(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](20, "mat-hint", 25);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](21, "strong");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](22);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](23, "div", 26);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](24, "mat-form-field");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](25, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](26, "Participants");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](27, "input", 27);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("blur", function BookingEditBookingsGroupComponent_div_8_Template_input_blur_27_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r55 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r55.vm.participants_count.change(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](28, "mat-hint");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](29, "Nombre d'h\u00F4tes");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](30, BookingEditBookingsGroupComponent_div_8_mat_error_30_Template, 2, 0, "mat-error", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](31, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](32, "div", 28);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](33, "mat-form-field");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](34, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](35, "Cat\u00E9gorie tarifaire");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](36, "input", 29);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("keyup", function BookingEditBookingsGroupComponent_div_8_Template_input_keyup_36_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r56 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r56.vm.rate_class.inputChange($event); })("focus", function BookingEditBookingsGroupComponent_div_8_Template_input_focus_36_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r57 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r57.vm.rate_class.focus(); })("blur", function BookingEditBookingsGroupComponent_div_8_Template_input_blur_36_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r58 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r58.vm.rate_class.restore(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](37, BookingEditBookingsGroupComponent_div_8_button_37_Template, 3, 0, "button", 30);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](38, "mat-autocomplete", 31, 32);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("optionSelected", function BookingEditBookingsGroupComponent_div_8_Template_mat_autocomplete_optionSelected_38_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r59 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r59.vm.rate_class.change($event); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](40, BookingEditBookingsGroupComponent_div_8_div_40_Template, 3, 2, "div", 17);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpipe"](41, "async");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](42, "mat-hint", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](43, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](44, "Cat\u00E9gorie tarifaire de facturation");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](45, "div", 34);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](46, "mat-form-field");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](47, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](48, "Type de s\u00E9jour");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](49, "mat-select", 35);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("selectionChange", function BookingEditBookingsGroupComponent_div_8_Template_mat_select_selectionChange_49_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r60 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r60.vm.sojourn_type.change($event); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](50, "mat-option", 36);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](51, "GG");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](52, "mat-option", 37);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](53, "GA");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](54, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](55, "div", 38);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](56, "mat-form-field", 39);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](57, "mat-slide-toggle", 40);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("change", function BookingEditBookingsGroupComponent_div_8_Template_mat_slide_toggle_change_57_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r51); const ctx_r61 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r61.vm.pack.change("has_pack", $event.checked); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](58, "Utiliser un pack ?");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelement"](59, "textarea", 41);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](60, BookingEditBookingsGroupComponent_div_8_div_60_Template, 13, 8, "div", 42);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](61, BookingEditBookingsGroupComponent_div_8_div_61_Template, 5, 1, "div", 43);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const _r21 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](19);
    const _r24 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](39);
    const ctx_r4 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](6);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("formControl", ctx_r4.vm.name.formControl);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !ctx_r4.vm.name.formControl.hasError("required"));
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](5);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("rangePicker", _r21);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("formControl", ctx_r4.vm.daterange.start.formControl);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("formControl", ctx_r4.vm.daterange.end.formControl);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("for", _r21);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("align", "end");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtextInterpolate2"]("", ctx_r4.vm.daterange.nights_count, " ", ctx_r4.vm.daterange.nights_count > 1 ? "nuit\u00E9es" : "nuit\u00E9e", "");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](5);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("value", ctx_r4.vm.participants_count.value)("formControl", ctx_r4.vm.participants_count.formControl);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !ctx_r4.vm.participants_count.formControl.hasError("required"));
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](6);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("matAutocomplete", _r24)("value", ctx_r4.vm.rate_class.name);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", ctx_r4.vm.rate_class.name.length);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("displayWith", ctx_r4.vm.rate_class.display);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpipeBind1"](41, 22, ctx_r4.vm.rate_class.filteredList));
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("align", "start");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngModel", ctx_r4.vm.sojourn_type.value);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](8);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngModel", ctx_r4.vm.pack.has_pack);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", ctx_r4.vm.pack.has_pack);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", ctx_r4.vm.pack.has_pack);
} }
function BookingEditBookingsGroupComponent_button_11_Template(rf, ctx) { if (rf & 1) {
    const _r63 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_button_11_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r63); _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](1); return _r0.products.folded = !_r0.products.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "keyboard_arrow_up");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_button_12_Template(rf, ctx) { if (rf & 1) {
    const _r65 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_button_12_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r65); _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](1); return _r0.products.folded = !_r0.products.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "keyboard_arrow_right");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_button_17_Template(rf, ctx) { if (rf & 1) {
    const _r67 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 51);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_button_17_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r67); const ctx_r66 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r66.vm.lines.add(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "add");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_21_div_20_button_5_Template(rf, ctx) { if (rf & 1) {
    const _r74 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_div_21_div_20_button_5_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r74); const line_r69 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"]().$implicit; const ctx_r72 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2); return ctx_r72.vm.lines.remove(line_r69); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "delete");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_21_div_20_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 60);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "div", 61);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "mat-icon", 62);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](3, "drag_indicator");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](4, "div", 63);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](5, BookingEditBookingsGroupComponent_div_21_div_20_button_5_Template, 3, 0, "button", 4);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelement"](6, "booking-edit-bookings-group-line", 64);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const line_r69 = ctx.$implicit;
    const index_r70 = ctx.index;
    const ctx_r68 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("cdkDragData", line_r69);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](5);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !ctx_r68.vm.pack.is_locked);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("groupInput", ctx_r68.group)("lineInput", ctx_r68._lineOutput[index_r70])("lineOutput", ctx_r68._lineInput);
} }
function BookingEditBookingsGroupComponent_div_21_Template(rf, ctx) { if (rf & 1) {
    const _r76 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "div", 52);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "div", 53);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](3, "div", 54);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](4, "div", 55);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](5, "SKU");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](6, "div", 56);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](7, "QT\u00C9");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](8, "div", 56);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](9, "GT\u00C9");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](10, "div", 56);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](11, "P.U.");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](12, "div", 56);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](13, "R\u00C9DUC");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](14, "div", 56);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](15, "TAX");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](16, "div", 56);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](17, "PRIX");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelement"](18, "div", 57);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](19, "div", 58);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("cdkDropListDropped", function BookingEditBookingsGroupComponent_div_21_Template_div_cdkDropListDropped_19_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r76); const ctx_r75 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); return ctx_r75.vm.lines.drop($event); });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](20, BookingEditBookingsGroupComponent_div_21_div_20_Template, 7, 5, "div", 59);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r8 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](20);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngForOf", ctx_r8.lines);
} }
function BookingEditBookingsGroupComponent_button_24_Template(rf, ctx) { if (rf & 1) {
    const _r78 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_button_24_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r78); _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](1); return _r0.accomodations.folded = !_r0.accomodations.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "keyboard_arrow_up");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_button_25_Template(rf, ctx) { if (rf & 1) {
    const _r80 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "button", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵlistener"]("click", function BookingEditBookingsGroupComponent_button_25_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵrestoreView"](_r80); _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](); const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](1); return _r0.accomodations.folded = !_r0.accomodations.folded; });
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "mat-icon", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](2, "keyboard_arrow_right");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_26_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "div", 65);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](3, "Logements ");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} }
function BookingEditBookingsGroupComponent_div_27_div_5_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 68);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelement"](1, "booking-edit-bookings-group-accomodation", 69);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const index_r83 = ctx.index;
    const ctx_r81 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("groupInput", ctx_r81.group)("accomodationInput", ctx_r81._accomodationOutput[index_r83])("accomodationOutput", ctx_r81._accomodationInput);
} }
function BookingEditBookingsGroupComponent_div_27_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](0, "div", 6);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](1, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "div", 65);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](3, "Logements ");
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](4, "div", 66);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](5, BookingEditBookingsGroupComponent_div_27_div_5_Template, 2, 3, "div", 67);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
} if (rf & 2) {
    const ctx_r12 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](5);
    _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngForOf", ctx_r12.accomodations);
} }
const _c0 = function () { return { folded: false }; };
const _c1 = function () { return { folded: true }; };
const _c2 = function (a0, a1, a2) { return { identification: a0, products: a1, accomodations: a2 }; };
class BookingEditBookingsGroupComponent {
    constructor(api, auth, dialog, zone, snack) {
        this.api = api;
        this.auth = auth;
        this.dialog = dialog;
        this.zone = zone;
        this.snack = snack;
        // observable for updates from children components
        this._lineInput = new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1);
        // array of observable for children components
        this._lineOutput = [];
        // observable for updates from children components
        this._accomodationInput = new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1);
        // array of observable for children components
        this._accomodationOutput = [];
        // fields for sub-items
        this.model_fields = {
            BookingLine: [
                "id", "product_id", "order", "qty", "price_id", "vat_rate", "unit_price", "price",
                "booking_id", "booking_line_group_id",
                "price_adapters_ids", "auto_discounts_ids", "manual_discounts_ids",
                "qty_accounting_method"
            ],
            Consumption: [
                "id", "product_id", "rental_unit_id"
            ]
        };
        this.ready = false;
        this.group = {};
        this.rate_class = null;
        this.pack = null;
        this.lines = [];
        this.accomodations = [];
        this.vm = {
            price: {
                value: 0
            },
            name: {
                value: '',
                formControl: new _angular_forms__WEBPACK_IMPORTED_MODULE_4__.FormControl('', _angular_forms__WEBPACK_IMPORTED_MODULE_4__.Validators.required),
                change: () => this.nameChange()
            },
            daterange: {
                start: {
                    formControl: new _angular_forms__WEBPACK_IMPORTED_MODULE_4__.FormControl()
                },
                end: {
                    formControl: new _angular_forms__WEBPACK_IMPORTED_MODULE_4__.FormControl()
                },
                nights_count: 0,
                change: () => this.dateRangeChange()
            },
            participants_count: {
                value: 1,
                formControl: new _angular_forms__WEBPACK_IMPORTED_MODULE_4__.FormControl('', _angular_forms__WEBPACK_IMPORTED_MODULE_4__.Validators.required),
                change: () => this.nbPersChange()
            },
            sojourn_type: {
                value: 'GG',
                change: (event) => this.sojournTypeChange(event)
            },
            pack: {
                name: '',
                has_pack: false,
                is_locked: false,
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_5__.Observable(),
                change: (target, value) => this.packChange(target, value),
                inputChange: (event) => this.packInputChange(event),
                focus: () => this.packFocus(),
                restore: () => this.packRestore(),
                reset: () => this.packReset(),
                display: (type) => this.packDisplay(type)
            },
            rate_class: {
                name: '',
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_5__.Observable(),
                change: (event) => this.rateClassChange(event),
                inputChange: (event) => this.rateClassInputChange(event),
                focus: () => this.rateClassFocus(),
                restore: () => this.rateClassRestore(),
                reset: () => this.rateClassReset(),
                display: (type) => this.rateClassDisplay(type)
            },
            lines: {
                add: () => this.lineAdd(),
                remove: (group) => this.lineRemove(group),
                drop: (event) => this.lineDrop(event)
            }
        };
    }
    ngOnInit() {
        // listen to the parent for changes on group object
        this.groupInput.subscribe((group) => this.load(group));
        // listen to changes relayed by children component on the _bookingInput observable
        this._lineInput.subscribe(params => this.updateFromLine(params));
        // listen to changes relayed by children component on the _bookingInput observable
        this._accomodationInput.subscribe(params => this.updateFromAccomodation(params));
        /**
         * listen to the changes on FormControl objects
         */
        this.vm.pack.filteredList = this.vm.pack.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_6__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_7__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_8__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () { return this.filterPacks(name); })));
        this.vm.rate_class.filteredList = this.vm.rate_class.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_6__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_7__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_8__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () { return this.filterRateClasses(name); })));
        this.vm.daterange.start.formControl.valueChanges.subscribe((date) => {
            this.dateRangeUpdate();
        });
        this.vm.daterange.end.formControl.valueChanges.subscribe((date) => {
            this.dateRangeUpdate();
        });
        this.vm.participants_count.formControl.valueChanges.subscribe((value) => {
            this.vm.participants_count.value = value;
        });
        this.vm.name.formControl.valueChanges.subscribe((value) => {
            this.vm.name.value = value;
        });
    }
    /**
     * Assign values from parent and load sub-objects required by the view.
     *
     * @param group
     */
    load(group) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            this.zone.run(() => {
                // this.ready = false;
            });
            this.zone.run(() => (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
                try {
                    console.log("BookingEditBookingsGroupComponent: received changes from parent", group.id, group);
                    // update local group object
                    for (let field of Object.keys(group)) {
                        this.group[field] = group[field];
                    }
                    if (group.name) {
                        this.vm.name.formControl.setValue(group.name);
                    }
                    if (group.hasOwnProperty('price')) {
                        this.vm.price.value = group.price;
                    }
                    if (group.rate_class_id) {
                        let data = yield this.api.read("sale\\customer\\RateClass", [group.rate_class_id], ["id", "name", "description"]);
                        if (data && data.length) {
                            let rate_class = data[0];
                            this.rate_class = rate_class;
                            this.vm.rate_class.name = rate_class.name;
                        }
                    }
                    if (group.pack_id) {
                        let data = yield this.api.read("lodging\\sale\\catalog\\Product", [group.pack_id], ["id", "name", "sku"]);
                        if (data && data.length) {
                            let pack = data[0];
                            this.pack = pack;
                            this.vm.pack.name = pack.name;
                        }
                        // we need to load related is_lock value
                    }
                    if (group.hasOwnProperty('has_pack')) {
                        this.vm.pack.has_pack = group.has_pack;
                    }
                    if (group.hasOwnProperty('is_locked')) {
                        this.vm.pack.is_locked = group.is_locked;
                    }
                    if (group.hasOwnProperty('nb_pers')) {
                        this.vm.participants_count.formControl.setValue(group.nb_pers);
                    }
                    if (group.sojourn_type) {
                        this.vm.sojourn_type.value = group.sojourn_type;
                    }
                    if (group.date_from) {
                        this.vm.daterange.start.formControl.setValue(group.date_from);
                    }
                    if (group.date_to) {
                        this.vm.daterange.end.formControl.setValue(group.date_to);
                    }
                    if (group.booking_lines_ids) {
                        let data = yield this.loadLines(group.booking_lines_ids, this.model_fields['BookingLine']);
                        if (data) {
                            for (let [index, line] of data.entries()) {
                                // add new lines (indexes from this.lines and this._lineOutput are synced)
                                if (index >= this.lines.length) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1);
                                    this._lineOutput.push(item);
                                    item.next(line);
                                    this.lines.push(line);
                                }
                                // if lines differ, overwrite previsously assigned line
                                else if (JSON.stringify(this.lines[index]) != JSON.stringify(line)) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1);
                                    this._lineOutput[index] = item;
                                    this.lines[index] = line;
                                    item.next(line);
                                }
                            }
                            // remove remaining lines, if any
                            if (this.lines.length > data.length) {
                                this.lines.splice(data.length);
                                this._lineOutput.splice(data.length);
                            }
                        }
                    }
                    if (group.accomodations_ids) {
                        let data = yield this.loadAccomodations(group.accomodations_ids, this.model_fields['Consumption']);
                        if (data) {
                            for (let [index, accomodation] of data.entries()) {
                                // add new lines (indexes from this.lines and this._lineOutput are synced)
                                if (index >= this.accomodations.length) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1);
                                    this._accomodationOutput.push(item);
                                    item.next(accomodation);
                                    this.accomodations.push(accomodation);
                                }
                                // if accomodations differ, overwrite previsously assigned line
                                else if (JSON.stringify(this.accomodations[index]) != JSON.stringify(accomodation)) {
                                    let item = new rxjs__WEBPACK_IMPORTED_MODULE_3__.ReplaySubject(1);
                                    this._accomodationOutput[index] = item;
                                    this.accomodations[index] = accomodation;
                                    item.next(accomodation);
                                }
                            }
                            // remove remaining accomodations, if any
                            if (this.accomodations.length > data.length) {
                                this.accomodations.splice(data.length);
                                this._accomodationOutput.splice(data.length);
                            }
                        }
                    }
                }
                catch (response) {
                    console.warn(response);
                }
                this.ready = true;
            }));
        });
    }
    updateFromAccomodation(line) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            console.log("BookingEditBookingsGroupComponent: received changes from child", line);
            let has_change = false;
            try {
                let index = this.lines.findIndex((element) => element.id == line.id);
                let t_line = this.lines.find((element) => element.id == line.id);
                if (line.hasOwnProperty('rental_unit_id') && line.rental_unit_id != t_line.rental_unit_id) {
                    has_change = true;
                    yield this.updateRentalUnit(line);
                }
                if (has_change) {
                    let data = yield this.loadAccomodations([line.id], this.model_fields['Consumption']);
                    let object = data[0];
                    for (let field of Object.keys(object)) {
                        this.accomodations[index][field] = object[field];
                    }
                    // relay changes to children components
                    this._accomodationOutput[index].next(this.accomodations[index]);
                    // notify User
                    this.snack.open("Logement mis à jour");
                }
            }
            catch (error) {
                console.warn(error);
            }
        });
    }
    /**
     * Handle update events received from BookingLine children.
     *
     */
    updateFromLine(line) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            console.log("BookingEditBookingsGroupComponent: received changes from child", line);
            try {
                let has_change = false;
                let refresh_requests = {};
                let index = this.lines.findIndex((element) => element.id == line.id);
                let t_line = this.lines.find((element) => element.id == line.id);
                if (line.hasOwnProperty('product_id') && line.product_id != t_line.product_id) {
                    yield this.updateProduct(line);
                    has_change = true;
                    // this implies reloading current group price and booking price        
                    refresh_requests['booking_id'] = ['price'];
                    refresh_requests['self'] = ['price'];
                }
                if (line.hasOwnProperty('qty') && line.qty != t_line.qty) {
                    yield this.updateQuantity(line);
                    has_change = true;
                    refresh_requests['booking_id'] = ['price'];
                    refresh_requests['self'] = ['price'];
                }
                if (line.hasOwnProperty('price_adapters_ids')) {
                    has_change = true;
                    refresh_requests['booking_id'] = ['price'];
                    refresh_requests['self'] = ['price'];
                }
                // handle explicit requests for updating single fields (reload partial object)
                if (line.hasOwnProperty('refresh')) {
                    // some changes have been done that might impact current object
                    // refresh property specifies which fields have to be re-loaded
                    let model_fields = this.model_fields['BookingLine'];
                    if (line.refresh.hasOwnProperty('self')) {
                        if (Array.isArray(line.refresh.self)) {
                            model_fields = line.refresh.self;
                        }
                        // reload object from server
                        let data = yield this.loadLines([line.id], model_fields);
                        this._lineOutput[index].next(data[0]);
                    }
                    // handle requests to relay to parent
                    if (line.refresh.hasOwnProperty('booking_line_group_id')) {
                        // line.refresh.booking_line_group_id is an array of fields from sale\booking\BookingLineGroup to be updated
                        if (refresh_requests.hasOwnPropertyKey('self')) {
                            refresh_requests['self'] = [...refresh_requests['self'], ...line.refresh.booking_line_group_id];
                        }
                        else {
                            refresh_requests['self'] = line.refresh.booking_line_group_id;
                        }
                    }
                }
                // reload whole object from server
                else if (has_change) {
                    let data = yield this.loadLines([line.id], this.model_fields['BookingLine']);
                    let object = data[0];
                    for (let field of Object.keys(object)) {
                        this.lines[index][field] = object[field];
                    }
                    // relay changes to children components
                    this._lineOutput[index].next(this.lines[index]);
                    // notify User
                    this.snack.open("Regroupement mis à jour");
                }
                // relay refresh request to parent, if any
                if (Object.keys(refresh_requests).length) {
                    this.groupOutput.next({ id: this.group.id, refresh: refresh_requests });
                }
            }
            catch (error) {
                console.warn('some changes could not be stored', error);
                this.snack.open("Erreur - certains changements n'ont pas pu être enregistrés.");
            }
        });
    }
    loadLines(ids, fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            let data = yield this.api.read("lodging\\sale\\booking\\BookingLine", ids, fields, 'order');
            return data;
        });
    }
    loadAccomodations(ids, fields) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            let data = yield this.api.read("lodging\\sale\\booking\\BookingLine", ids, fields);
            return data;
        });
    }
    dateRangeUpdate() {
        let start = this.vm.daterange.start.formControl.value;
        let end = this.vm.daterange.end.formControl.value;
        if (start && end) {
            let diff = Math.floor((Date.parse(end.toString()) - Date.parse(start.toString())) / (60 * 60 * 24 * 1000));
            this.vm.daterange.nights_count = (diff < 0) ? 0 : diff;
        }
    }
    nbPersChange() {
        console.log('BookingEditCustomerComponent::nbPersChange');
        // relay change to parent component
        this.groupOutput.next({ id: this.group.id, nb_pers: this.vm.participants_count.value, refresh: { self: ['price'], booking_id: ['price'] } });
    }
    nameChange() {
        console.log('BookingEditCustomerComponent::nameChange');
        // relay change to parent component
        this.groupOutput.next({ id: this.group.id, name: this.vm.name.value });
    }
    dateRangeChange() {
        console.log('BookingEditCustomerComponent::dateRangeChange');
        // relay change to parent component
        this.groupOutput.next({ id: this.group.id, date_from: this.vm.daterange.start.formControl.value, date_to: this.vm.daterange.end.formControl.value });
    }
    packChange(target, value) {
        console.log('BookingEditCustomerComponent::packChange', value);
        switch (target) {
            case 'name':
                let pack = value;
                this.pack = pack;
                this.vm.pack.name = pack.name;
                // relay change to parent component
                this.groupOutput.next({ id: this.group.id, pack_id: pack.id });
                break;
            case 'has_pack':
                this.vm.pack.has_pack = value;
                // relay change to parent component
                this.groupOutput.next({ id: this.group.id, has_pack: value });
                break;
            case 'is_locked':
                this.vm.pack.is_locked = value;
                // relay change to parent component
                this.groupOutput.next({ id: this.group.id, is_locked: value });
                break;
        }
    }
    packInputChange(event) {
        this.vm.pack.inputClue.next(event.target.value);
    }
    packFocus() {
        this.vm.pack.inputClue.next("");
    }
    packDisplay(pack) {
        return pack ? pack.name : '';
    }
    packReset() {
        setTimeout(() => {
            this.vm.pack.name = '';
        }, 100);
    }
    packRestore() {
        if (this.pack) {
            this.vm.pack.name = this.pack.name;
        }
        else {
            this.vm.pack.name = '';
        }
    }
    sojournTypeChange(event) {
    }
    rateClassChange(event) {
        console.log('BookingEditCustomerComponent::rateClassChange', event);
        // from mat-autocomplete
        if (event && event.option && event.option.value) {
            let rate_class = event.option.value;
            this.rate_class = rate_class;
            this.vm.rate_class.name = rate_class.name;
            // relay change to parent component
            // this.bookingOutput.next({type_id: type.id});
            this.groupOutput.next({ id: this.group.id, rate_class_id: rate_class.id });
        }
    }
    rateClassInputChange(event) {
        this.vm.rate_class.inputClue.next(event.target.value);
    }
    rateClassFocus() {
        this.vm.rate_class.inputClue.next("");
    }
    rateClassDisplay(rate_class) {
        return rate_class ? (rate_class.name + ' - ' + rate_class.description) : '';
    }
    rateClassReset() {
        setTimeout(() => {
            this.vm.rate_class.name = '';
        }, 100);
    }
    rateClassRestore() {
        if (this.rate_class) {
            this.vm.rate_class.name = this.rate_class.name;
        }
        else {
            this.vm.rate_class.name = '';
        }
    }
    filterPacks(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            /*
            #todo - limit packages to the ones available for currently selected center
            $families_ids = center.product_families_ids
            $products = sale\catalog\Product::search(['family_id', 'in', $families_ids])
            */
            let filtered = [];
            try {
                let data = yield this.api.collect("lodging\\sale\\catalog\\Product", [["name", "ilike", '%' + name + '%'], ["is_pack", "=", "true"]], ["id", "name"], 'name', 'asc', 0, 25);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
    filterRateClasses(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            let filtered = [];
            try {
                let data = yield this.api.collect("sale\\customer\\RateClass", [["name", "ilike", '%' + name + '%']], ["id", "name", "description"], 'name', 'asc', 0, 25);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
    lineAdd() {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            try {
                const line = yield this.api.create("lodging\\sale\\booking\\BookingLine", {
                    order: this.lines.length + 1,
                    booking_id: this.group.booking_id,
                    booking_line_group_id: this.group.id
                });
                // emit change to parent
                this.groupOutput.next({ id: this.group.id, booking_lines_ids: [line.id] });
            }
            catch (error) {
                console.log(error);
            }
        });
    }
    /**
     * Emit change to parent for partial update.
     * @param line
     */
    lineRemove(line) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            this.groupOutput.next({ id: this.group.id, booking_lines_ids: [-line.id] });
        });
    }
    lineDrop(event) {
        (0,_angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_10__.moveItemInArray)(this.lines, event.previousIndex, event.currentIndex);
        (0,_angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_10__.moveItemInArray)(this._lineOutput, event.previousIndex, event.currentIndex);
        // adapt new values for 'order' field
        for (let index in this.lines) {
            let item = this.lines[index];
            this.updateFromLine({ id: item.id, order: parseInt(index) + 1 });
        }
    }
    updateProduct(line) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            yield this.api.update("lodging\\sale\\booking\\BookingLine", [line.id], { "product_id": line.product_id });
        });
    }
    updateQuantity(line) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            yield this.api.update("lodging\\sale\\booking\\BookingLine", [line.id], { "qty": line.qty });
        });
    }
    updateRentalUnit(line) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_9__.__awaiter)(this, void 0, void 0, function* () {
            yield this.api.update("lodging\\sale\\booking\\BookingLine", [line.id], { "rental_unit_id": line.rental_unit_id });
        });
    }
}
BookingEditBookingsGroupComponent.ɵfac = function BookingEditBookingsGroupComponent_Factory(t) { return new (t || BookingEditBookingsGroupComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_11__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_11__.AuthService), _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_12__.MatDialog), _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_2__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵdirectiveInject"](_angular_material_snack_bar__WEBPACK_IMPORTED_MODULE_13__.MatSnackBar)); };
BookingEditBookingsGroupComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵdefineComponent"]({ type: BookingEditBookingsGroupComponent, selectors: [["booking-edit-bookings-group"]], inputs: { bookingInput: "bookingInput", groupOutput: "groupOutput", groupInput: "groupInput" }, decls: 28, vars: 26, consts: [[3, "var"], ["vGroup", "var"], [1, "part"], [1, "part-toggle"], ["mat-icon-button", "", 3, "click", 4, "ngIf"], ["class", "part-container", 4, "ngIf"], [1, "part-container"], [1, "row"], [1, "row-products-title"], ["mat-mini-fab", "", "color", "primary", "style", "transform: scale(0.65);", 3, "click", 4, "ngIf"], [1, "row-products-price"], ["class", "row", 4, "ngIf"], ["mat-icon-button", "", 3, "click"], [2, "font-size", "15px"], [1, "", 2, "min-width", "350px"], [1, "header"], ["type", "text", "matInput", "", 3, "formControl", "blur"], [4, "ngIf"], [1, "cell", 2, "max-width", "260px"], [3, "rangePicker"], ["matStartDate", "", "placeholder", "dd/mm/yyyy", 3, "formControl", "blur"], ["matEndDate", "", "placeholder", "dd/mm/yyyy", 3, "formControl", "blur"], ["matSuffix", "", 3, "for"], [3, "closed"], ["sojournDateRangePicker", ""], [3, "align"], [1, "cell", 2, "max-width", "150px"], ["type", "number", "matInput", "", 3, "value", "formControl", "blur"], [1, "cell", 2, "max-width", "250px"], ["matInput", "", "type", "text", "placeholder", "Commencez \u00E0 taper le nom", 3, "matAutocomplete", "value", "keyup", "focus", "blur"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click", 4, "ngIf"], [3, "displayWith", "optionSelected"], ["rateClassAutocomplete", "matAutocomplete"], [2, "opacity", "1", 3, "align"], [1, "cell", 2, "max-width", "120px"], [3, "ngModel", "selectionChange"], ["value", "GG"], ["value", "GA"], [1, "cell", 2, "max-width", "200px"], ["floatLabel", "always", 1, "invisible"], ["color", "primary", 3, "ngModel", "change"], ["matInput", "", "hidden", ""], ["class", "cell", "style", "max-width: 350px;", 4, "ngIf"], ["class", "cell", 4, "ngIf"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click"], [3, "value", 4, "ngFor", "ngForOf"], [3, "value"], [1, "cell", 2, "max-width", "350px"], ["packAutocomplete", "matAutocomplete"], [1, "cell"], [3, "ngModel", "change"], ["mat-mini-fab", "", "color", "primary", 2, "transform", "scale(0.65)", 3, "click"], [2, "width", "100%"], [1, "products-header"], [2, "display", "flex", "width", "100%"], [1, "product-cell", 2, "text-align", "left"], [1, "product-cell"], [1, "product-cell", 2, "max-width", "35px"], ["cdkDropList", "", 1, "products-list", 3, "cdkDropListDropped"], ["class", "product-item", "cdkDrag", "", 3, "cdkDragData", 4, "ngFor", "ngForOf"], ["cdkDrag", "", 1, "product-item", 3, "cdkDragData"], ["cdkDragHandle", "", 1, "product-handle"], [2, "font-size", "16px"], [1, "product-remove"], [3, "groupInput", "lineInput", "lineOutput"], [2, "margin-top", "8px"], [1, "row", 2, "display", "block"], ["class", "accomodation-item", 4, "ngFor", "ngForOf"], [1, "accomodation-item"], [3, "groupInput", "accomodationInput", "accomodationOutput"]], template: function BookingEditBookingsGroupComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelement"](0, "div", 0, 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](2, "div");
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](3, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](4, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](5, BookingEditBookingsGroupComponent_button_5_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](6, BookingEditBookingsGroupComponent_button_6_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](7, BookingEditBookingsGroupComponent_div_7_Template, 10, 2, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](8, BookingEditBookingsGroupComponent_div_8_Template, 62, 24, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](9, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](10, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](11, BookingEditBookingsGroupComponent_button_11_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](12, BookingEditBookingsGroupComponent_button_12_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](13, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](14, "div", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](15, "div", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](16, "Produits ");
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](17, BookingEditBookingsGroupComponent_button_17_Template, 3, 0, "button", 9);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](18, "div", 10);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtext"](19);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpipe"](20, "number");
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](21, BookingEditBookingsGroupComponent_div_21_Template, 21, 1, "div", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](22, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementStart"](23, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](24, BookingEditBookingsGroupComponent_button_24_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](25, BookingEditBookingsGroupComponent_button_25_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](26, BookingEditBookingsGroupComponent_div_26_Template, 4, 0, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtemplate"](27, BookingEditBookingsGroupComponent_div_27_Template, 6, 1, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵelementEnd"]();
    } if (rf & 2) {
        const _r0 = _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵreference"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("var", _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpureFunction3"](22, _c2, _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpureFunction0"](19, _c0), _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpureFunction0"](20, _c0), _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpureFunction0"](21, _c1)));
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵclassProp"]("hidden", !ctx.ready);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !_r0.identification.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", _r0.identification.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", _r0.identification.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !_r0.identification.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !_r0.products.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", _r0.products.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](5);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !ctx.vm.pack.is_locked);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵtextInterpolate1"]("TOTAL TTC \u00A0:\u00A0 ", _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵpipeBind2"](20, 16, ctx.vm.price.value, "1.2-2"), " \u20AC");
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !_r0.products.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](3);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !_r0.accomodations.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", _r0.accomodations.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", _r0.accomodations.folded);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_2__["ɵɵproperty"]("ngIf", !_r0.accomodations.folded);
    } }, directives: [sb_shared_lib__WEBPACK_IMPORTED_MODULE_11__.VarDirective, _angular_common__WEBPACK_IMPORTED_MODULE_14__.NgIf, _angular_material_button__WEBPACK_IMPORTED_MODULE_15__.MatButton, _angular_material_icon__WEBPACK_IMPORTED_MODULE_16__.MatIcon, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_17__.MatFormField, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_17__.MatLabel, _angular_material_input__WEBPACK_IMPORTED_MODULE_18__.MatInput, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.DefaultValueAccessor, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.NgControlStatus, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.FormControlDirective, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_17__.MatHint, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_17__.MatError, _angular_material_datepicker__WEBPACK_IMPORTED_MODULE_19__.MatDateRangeInput, _angular_material_datepicker__WEBPACK_IMPORTED_MODULE_19__.MatStartDate, _angular_material_datepicker__WEBPACK_IMPORTED_MODULE_19__.MatEndDate, _angular_material_datepicker__WEBPACK_IMPORTED_MODULE_19__.MatDatepickerToggle, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_17__.MatSuffix, _angular_material_datepicker__WEBPACK_IMPORTED_MODULE_19__.MatDateRangePicker, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.NumberValueAccessor, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_20__.MatAutocompleteTrigger, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_20__.MatAutocomplete, _angular_material_select__WEBPACK_IMPORTED_MODULE_21__.MatSelect, _angular_forms__WEBPACK_IMPORTED_MODULE_4__.NgModel, _angular_material_core__WEBPACK_IMPORTED_MODULE_22__.MatOption, _angular_material_slide_toggle__WEBPACK_IMPORTED_MODULE_23__.MatSlideToggle, _angular_common__WEBPACK_IMPORTED_MODULE_14__.NgForOf, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_10__.CdkDropList, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_10__.CdkDrag, _angular_cdk_drag_drop__WEBPACK_IMPORTED_MODULE_10__.CdkDragHandle, _booking_edit_bookings_group_line_booking_edit_bookings_group_line_component__WEBPACK_IMPORTED_MODULE_0__.BookingEditBookingsGroupLineComponent, _booking_edit_bookings_group_accomodation_booking_edit_bookings_group_accomodation_component__WEBPACK_IMPORTED_MODULE_1__.BookingEditBookingsGroupAccomodationComponent], pipes: [_angular_common__WEBPACK_IMPORTED_MODULE_14__.DecimalPipe, _angular_common__WEBPACK_IMPORTED_MODULE_14__.AsyncPipe], styles: ["[_nghost-%COMP%] {\n  display: block;\n  width: 100%;\n  height: 100%;\n  padding-top: 10px;\n}\n[_nghost-%COMP%]   .hidden[_ngcontent-%COMP%] {\n  visibility: hidden;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%] {\n  display: flex;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-toggle[_ngcontent-%COMP%] {\n  flex: 0 1;\n  display: inline-block;\n  vertical-align: top;\n  padding: 5px 0px 0px 20px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%] {\n  flex: 1;\n  display: inline-block;\n  width: 100%;\n  min-height: 45px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%] {\n  width: 100%;\n  display: flex;\n  padding: 5px 0;\n  flex: 1;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .row-products-title[_ngcontent-%COMP%] {\n  flex: 0 1 50%;\n  height: 40px;\n  line-height: 40px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .row-products-price[_ngcontent-%COMP%] {\n  flex: 0 1 20%;\n  margin-left: auto;\n  text-align: right;\n  margin-right: 65px;\n}\n[_nghost-%COMP%]   .part[_ngcontent-%COMP%]   .part-container[_ngcontent-%COMP%]   .row[_ngcontent-%COMP%]   .cell[_ngcontent-%COMP%] {\n  flex: 1;\n}\n[_nghost-%COMP%]   mat-form-field[_ngcontent-%COMP%] {\n  width: 100%;\n  padding: 0px 12px;\n}\n[_nghost-%COMP%]   mat-form-field.header[_ngcontent-%COMP%] {\n  margin-top: -5px;\n}\n[_nghost-%COMP%]   mat-form-field.header[_ngcontent-%COMP%]   input[_ngcontent-%COMP%] {\n  font-size: 22px;\n}\n[_nghost-%COMP%]   mat-form-field.invisible[_ngcontent-%COMP%]    .mat-form-field-underline {\n  display: none !important;\n}\n[_nghost-%COMP%]   p[_ngcontent-%COMP%] {\n  font-size: 14px;\n  padding: 0px 12px;\n}\n[_nghost-%COMP%]   .products-header[_ngcontent-%COMP%] {\n  padding: 0 12px 0 0;\n  width: 100%;\n  margin-bottom: 1px;\n}\n[_nghost-%COMP%]   .products-header[_ngcontent-%COMP%]   .product-cell[_ngcontent-%COMP%] {\n  font-weight: 600;\n  outline: solid 1px rgba(0, 0, 0, 0.2);\n  line-height: 34px;\n  position: relative;\n  flex: 1 0 8%;\n  text-align: right;\n  padding: 0 10px;\n  margin-right: 15px;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n}\n[_nghost-%COMP%]   .products-header[_ngcontent-%COMP%]   .product-cell[_ngcontent-%COMP%]:last-child {\n  outline: none;\n}\n[_nghost-%COMP%]   .products-header[_ngcontent-%COMP%]   .product-cell[_ngcontent-%COMP%]:first-child {\n  flex: 1 0 40%;\n}\n[_nghost-%COMP%]   .products-list[_ngcontent-%COMP%] {\n  position: relative;\n  padding: 0 12px 0 0;\n  width: 100%;\n}\n[_nghost-%COMP%]   .products-list[_ngcontent-%COMP%]   .product-item[_ngcontent-%COMP%] {\n  position: relative;\n  background-color: white;\n}\n[_nghost-%COMP%]   .products-list[_ngcontent-%COMP%]   .product-item[_ngcontent-%COMP%]   .product-handle[_ngcontent-%COMP%] {\n  position: absolute;\n  top: 10px;\n  left: 5px;\n  z-index: 3;\n}\n[_nghost-%COMP%]   .products-list[_ngcontent-%COMP%]   .product-item[_ngcontent-%COMP%]   .product-remove[_ngcontent-%COMP%] {\n  position: absolute;\n  right: -5px;\n  top: 10px;\n  z-index: 2;\n}\n.cdk-drag-preview[_ngcontent-%COMP%] {\n  box-sizing: border-box;\n  border-radius: 4px;\n  box-shadow: 0 5px 5px -3px rgba(0, 0, 0, 0.2), 0 8px 10px 1px rgba(0, 0, 0, 0.14), 0 3px 14px 2px rgba(0, 0, 0, 0.12);\n  background-color: lightgrey !important;\n}\n.cdk-drag-preview[_ngcontent-%COMP%]   .product-handle[_ngcontent-%COMP%] {\n  display: none;\n}\n.cdk-drag-preview[_ngcontent-%COMP%]   .product-remove[_ngcontent-%COMP%] {\n  display: none;\n}\n.cdk-drag-placeholder[_ngcontent-%COMP%] {\n  opacity: 0;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5ib29raW5ncy5ncm91cC5jb21wb25lbnQuc2NzcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUNFLGNBQUE7RUFDQSxXQUFBO0VBQ0EsWUFBQTtFQUVBLGlCQUFBO0FBQUY7QUFFRTtFQUNFLGtCQUFBO0FBQUo7QUFHRTtFQUNFLGFBQUE7QUFESjtBQUdJO0VBQ0UsU0FBQTtFQUNBLHFCQUFBO0VBQ0EsbUJBQUE7RUFDQSx5QkFBQTtBQUROO0FBSUk7RUFDRSxPQUFBO0VBQ0EscUJBQUE7RUFDQSxXQUFBO0VBQ0EsZ0JBQUE7QUFGTjtBQUlNO0VBQ0UsV0FBQTtFQUNBLGFBQUE7RUFDQSxjQUFBO0VBQ0EsT0FBQTtBQUZSO0FBSVE7RUFDRSxhQUFBO0VBQ0EsWUFBQTtFQUNBLGlCQUFBO0FBRlY7QUFLUTtFQUNFLGFBQUE7RUFDQSxpQkFBQTtFQUNBLGlCQUFBO0VBQ0Esa0JBQUE7QUFIVjtBQU1RO0VBQ0UsT0FBQTtBQUpWO0FBVUU7RUFDRSxXQUFBO0VBQ0EsaUJBQUE7QUFSSjtBQVlFO0VBQ0UsZ0JBQUE7QUFWSjtBQVlJO0VBQ0UsZUFBQTtBQVZOO0FBZUk7RUFDRSx3QkFBQTtBQWJOO0FBaUJFO0VBQ0UsZUFBQTtFQUNBLGlCQUFBO0FBZko7QUFrQkU7RUFDRSxtQkFBQTtFQUNBLFdBQUE7RUFDQSxrQkFBQTtBQWhCSjtBQWtCSTtFQUNFLGdCQUFBO0VBQ0EscUNBQUE7RUFDQSxpQkFBQTtFQUNBLGtCQUFBO0VBQ0EsWUFBQTtFQUNBLGlCQUFBO0VBQ0EsZUFBQTtFQUNBLGtCQUFBO0VBQ0EsbUJBQUE7RUFDQSx1QkFBQTtFQUNBLGdCQUFBO0FBaEJOO0FBbUJJO0VBQ0UsYUFBQTtBQWpCTjtBQW9CSTtFQUNFLGFBQUE7QUFsQk47QUFzQkU7RUFDRSxrQkFBQTtFQUNBLG1CQUFBO0VBQ0EsV0FBQTtBQXBCSjtBQXVCSTtFQUNFLGtCQUFBO0VBQ0EsdUJBQUE7QUFyQk47QUF1Qk07RUFDRSxrQkFBQTtFQUNBLFNBQUE7RUFDQSxTQUFBO0VBQ0EsVUFBQTtBQXJCUjtBQXdCTTtFQUNFLGtCQUFBO0VBQ0EsV0FBQTtFQUNBLFNBQUE7RUFDQSxVQUFBO0FBdEJSO0FBaUNBO0VBQ0Usc0JBQUE7RUFDQSxrQkFBQTtFQUNBLHFIQUFBO0VBR0Esc0NBQUE7QUFoQ0Y7QUFrQ0U7RUFDRSxhQUFBO0FBaENKO0FBbUNFO0VBQ0UsYUFBQTtBQWpDSjtBQXFDQTtFQUNFLFVBQUE7QUFsQ0YiLCJmaWxlIjoiYm9va2luZy5lZGl0LmJvb2tpbmdzLmdyb3VwLmNvbXBvbmVudC5zY3NzIiwic291cmNlc0NvbnRlbnQiOlsiOmhvc3Qge1xyXG4gIGRpc3BsYXk6IGJsb2NrO1xyXG4gIHdpZHRoOiAxMDAlO1xyXG4gIGhlaWdodDogMTAwJTtcclxuXHJcbiAgcGFkZGluZy10b3A6IDEwcHg7XHJcblxyXG4gIC5oaWRkZW4ge1xyXG4gICAgdmlzaWJpbGl0eTogaGlkZGVuO1xyXG4gIH1cclxuICBcclxuICAucGFydCB7XHJcbiAgICBkaXNwbGF5OiBmbGV4O1xyXG5cclxuICAgIC5wYXJ0LXRvZ2dsZSB7XHJcbiAgICAgIGZsZXg6IDAgMTtcclxuICAgICAgZGlzcGxheTogaW5saW5lLWJsb2NrO1xyXG4gICAgICB2ZXJ0aWNhbC1hbGlnbjogdG9wO1xyXG4gICAgICBwYWRkaW5nOiA1cHggMHB4IDBweCAyMHB4OyAgICAgIFxyXG4gICAgfVxyXG5cclxuICAgIC5wYXJ0LWNvbnRhaW5lciB7XHJcbiAgICAgIGZsZXg6IDE7XHJcbiAgICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcclxuICAgICAgd2lkdGg6IDEwMCU7XHJcbiAgICAgIG1pbi1oZWlnaHQ6IDQ1cHg7XHJcblxyXG4gICAgICAucm93IHtcclxuICAgICAgICB3aWR0aDogMTAwJTtcclxuICAgICAgICBkaXNwbGF5OiBmbGV4O1xyXG4gICAgICAgIHBhZGRpbmc6IDVweCAwO1xyXG4gICAgICAgIGZsZXg6IDE7XHJcblxyXG4gICAgICAgIC5yb3ctcHJvZHVjdHMtdGl0bGUge1xyXG4gICAgICAgICAgZmxleDogMCAxIDUwJTtcclxuICAgICAgICAgIGhlaWdodDogNDBweDtcclxuICAgICAgICAgIGxpbmUtaGVpZ2h0OiA0MHB4O1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgLnJvdy1wcm9kdWN0cy1wcmljZSB7XHJcbiAgICAgICAgICBmbGV4OiAwIDEgMjAlO1xyXG4gICAgICAgICAgbWFyZ2luLWxlZnQ6IGF1dG87XHJcbiAgICAgICAgICB0ZXh0LWFsaWduOiByaWdodDtcclxuICAgICAgICAgIG1hcmdpbi1yaWdodDogNjVweDtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIC5jZWxsIHtcclxuICAgICAgICAgIGZsZXg6IDE7IFxyXG4gICAgICAgIH0gICAgXHJcbiAgICAgfVxyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgbWF0LWZvcm0tZmllbGQge1xyXG4gICAgd2lkdGg6IDEwMCU7XHJcbiAgICBwYWRkaW5nOiAwcHggMTJweDtcclxuXHJcbiAgfVxyXG5cclxuICBtYXQtZm9ybS1maWVsZC5oZWFkZXIge1xyXG4gICAgbWFyZ2luLXRvcDogLTVweDtcclxuICBcclxuICAgIGlucHV0IHtcclxuICAgICAgZm9udC1zaXplOiAyMnB4O1xyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgbWF0LWZvcm0tZmllbGQuaW52aXNpYmxlIHtcclxuICAgIDo6bmctZGVlcC5tYXQtZm9ybS1maWVsZC11bmRlcmxpbmUge1xyXG4gICAgICBkaXNwbGF5OiBub25lICFpbXBvcnRhbnQ7IFxyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgcCB7XHJcbiAgICBmb250LXNpemU6IDE0cHg7XHJcbiAgICBwYWRkaW5nOiAwcHggMTJweDtcclxuICB9XHJcblxyXG4gIC5wcm9kdWN0cy1oZWFkZXIge1xyXG4gICAgcGFkZGluZzogMCAxMnB4IDAgMDtcclxuICAgIHdpZHRoOiAxMDAlO1xyXG4gICAgbWFyZ2luLWJvdHRvbTogMXB4O1xyXG5cclxuICAgIC5wcm9kdWN0LWNlbGwge1xyXG4gICAgICBmb250LXdlaWdodDogNjAwO1xyXG4gICAgICBvdXRsaW5lOiBzb2xpZCAxcHggcmdiYSgwLDAsMCwwLjIpO1xyXG4gICAgICBsaW5lLWhlaWdodDogMzRweDtcclxuICAgICAgcG9zaXRpb246IHJlbGF0aXZlO1xyXG4gICAgICBmbGV4OiAxIDAgOCU7XHJcbiAgICAgIHRleHQtYWxpZ246IHJpZ2h0O1xyXG4gICAgICBwYWRkaW5nOiAwIDEwcHg7XHJcbiAgICAgIG1hcmdpbi1yaWdodDogMTVweDtcclxuICAgICAgd2hpdGUtc3BhY2U6IG5vd3JhcDtcclxuICAgICAgdGV4dC1vdmVyZmxvdzogZWxsaXBzaXM7XHJcbiAgICAgIG92ZXJmbG93OiBoaWRkZW47XHJcbiAgICB9XHJcblxyXG4gICAgLnByb2R1Y3QtY2VsbDpsYXN0LWNoaWxkIHtcclxuICAgICAgb3V0bGluZTogbm9uZTtcclxuICAgIH1cclxuICAgIFxyXG4gICAgLnByb2R1Y3QtY2VsbDpmaXJzdC1jaGlsZCB7XHJcbiAgICAgIGZsZXg6IDEgMCA0MCU7ICAgIFxyXG4gICAgfSAgICBcclxuICB9XHJcblxyXG4gIC5wcm9kdWN0cy1saXN0IHtcclxuICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcclxuICAgIHBhZGRpbmc6IDAgMTJweCAwIDA7XHJcbiAgICB3aWR0aDogMTAwJTtcclxuXHJcblxyXG4gICAgLnByb2R1Y3QtaXRlbSB7XHJcbiAgICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcclxuICAgICAgYmFja2dyb3VuZC1jb2xvcjogd2hpdGU7XHJcbiAgXHJcbiAgICAgIC5wcm9kdWN0LWhhbmRsZSB7XHJcbiAgICAgICAgcG9zaXRpb246IGFic29sdXRlO1xyXG4gICAgICAgIHRvcDogMTBweDtcclxuICAgICAgICBsZWZ0OiA1cHg7XHJcbiAgICAgICAgei1pbmRleDogMztcclxuICAgICAgfVxyXG4gIFxyXG4gICAgICAucHJvZHVjdC1yZW1vdmUge1xyXG4gICAgICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTtcclxuICAgICAgICByaWdodDogLTVweDtcclxuICAgICAgICB0b3A6IDEwcHg7XHJcbiAgICAgICAgei1pbmRleDogMjtcclxuICAgICAgfVxyXG4gICAgXHJcbiAgICB9ICAgIFxyXG4gICAgXHJcbiAgfVxyXG5cclxuXHJcbiAgXHJcbn1cclxuXHJcbi5jZGstZHJhZy1wcmV2aWV3IHtcclxuICBib3gtc2l6aW5nOiBib3JkZXItYm94O1xyXG4gIGJvcmRlci1yYWRpdXM6IDRweDtcclxuICBib3gtc2hhZG93OiAwIDVweCA1cHggLTNweCByZ2JhKDAsIDAsIDAsIDAuMiksXHJcbiAgICAgICAgICAgICAgMCA4cHggMTBweCAxcHggcmdiYSgwLCAwLCAwLCAwLjE0KSxcclxuICAgICAgICAgICAgICAwIDNweCAxNHB4IDJweCByZ2JhKDAsIDAsIDAsIDAuMTIpO1xyXG4gIGJhY2tncm91bmQtY29sb3I6IGxpZ2h0Z3JleSAhaW1wb3J0YW50O1xyXG5cclxuICAucHJvZHVjdC1oYW5kbGUge1xyXG4gICAgZGlzcGxheTogbm9uZTtcclxuICB9XHJcblxyXG4gIC5wcm9kdWN0LXJlbW92ZSB7XHJcbiAgICBkaXNwbGF5OiBub25lO1xyXG4gIH1cclxufVxyXG5cclxuLmNkay1kcmFnLXBsYWNlaG9sZGVyIHtcclxuICBvcGFjaXR5OiAwO1xyXG59Il19 */"] });


/***/ }),

/***/ 6177:
/*!******************************************************************************************************!*\
  !*** ./src/app/in/bookings/edit/components/booking.edit.customer/booking.edit.customer.component.ts ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditCustomerComponent": () => (/* binding */ BookingEditCustomerComponent),
/* harmony export */   "DialogCreatePartner": () => (/* binding */ DialogCreatePartner)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @angular/material/dialog */ 2238);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! rxjs */ 8229);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! rxjs */ 9165);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! rxjs/operators */ 4395);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! rxjs/operators */ 8002);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! rxjs/operators */ 9773);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @angular/material/form-field */ 8295);
/* harmony import */ var _angular_material_input__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @angular/material/input */ 3166);
/* harmony import */ var _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @angular/material/autocomplete */ 1554);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _angular_material_slide_toggle__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @angular/material/slide-toggle */ 5396);
/* harmony import */ var _angular_forms__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @angular/forms */ 3679);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_icon__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! @angular/material/icon */ 6627);
/* harmony import */ var _angular_material_core__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! @angular/material/core */ 7817);
















function BookingEditCustomerComponent_button_6_Template(rf, ctx) { if (rf & 1) {
    const _r5 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "button", 10);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditCustomerComponent_button_6_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r5); const ctx_r4 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r4.vm.customer.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditCustomerComponent_div_9_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const customer_r9 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("value", customer_r9);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", customer_r9.name, " ");
} }
function BookingEditCustomerComponent_div_9_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditCustomerComponent_div_9_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](1, BookingEditCustomerComponent_div_9_mat_option_1_Template, 2, 2, "mat-option", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](2, BookingEditCustomerComponent_div_9_mat_option_2_Template, 3, 0, "mat-option", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r6 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngForOf", list_r6);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", list_r6.length == 0);
} }
function BookingEditCustomerComponent_div_18_button_5_Template(rf, ctx) { if (rf & 1) {
    const _r14 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "button", 10);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditCustomerComponent_div_18_button_5_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r14); const ctx_r13 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](2); return ctx_r13.vm.payer.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditCustomerComponent_div_18_div_8_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option", 12);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const payer_r18 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("value", payer_r18);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", payer_r18.name, " ");
} }
function BookingEditCustomerComponent_div_18_div_8_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditCustomerComponent_div_18_div_8_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](1, BookingEditCustomerComponent_div_18_div_8_mat_option_1_Template, 2, 2, "mat-option", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](2, BookingEditCustomerComponent_div_18_div_8_mat_option_2_Template, 3, 0, "mat-option", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r15 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngForOf", list_r15);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", list_r15.length == 0);
} }
function BookingEditCustomerComponent_div_18_Template(rf, ctx) { if (rf & 1) {
    const _r20 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-form-field", 13);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "mat-label");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](3, "Organisme payeur");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "input", 14);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("keyup", function BookingEditCustomerComponent_div_18_Template_input_keyup_4_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r20); const ctx_r19 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r19.vm.payer.inputChange($event); })("focus", function BookingEditCustomerComponent_div_18_Template_input_focus_4_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r20); const ctx_r21 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r21.vm.payer.focus(); })("blur", function BookingEditCustomerComponent_div_18_Template_input_blur_4_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r20); const ctx_r22 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r22.vm.payer.restore(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](5, BookingEditCustomerComponent_div_18_button_5_Template, 3, 0, "button", 4);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](6, "mat-autocomplete", 5, 15);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("optionSelected", function BookingEditCustomerComponent_div_18_Template_mat_autocomplete_optionSelected_6_listener($event) { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r20); const ctx_r23 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r23.vm.payer.change($event); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](8, BookingEditCustomerComponent_div_18_div_8_Template, 3, 2, "div", 7);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipe"](9, "async");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](10, "mat-hint", 8);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](11, "span");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](12, "S\u00E9lection de l'organisme payeur");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](13, "button", 16);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditCustomerComponent_div_18_Template_button_click_13_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r20); const ctx_r24 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r24.selectPayer(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](14, "Ajouter un organisme payeur");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const _r11 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](7);
    const ctx_r3 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](4);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("matAutocomplete", _r11)("value", ctx_r3.vm.payer.name);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx_r3.vm.payer.name.length);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("displayWith", ctx_r3.vm.payer.display);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipeBind1"](9, 6, ctx_r3.vm.payer.filteredList));
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("align", "start");
} }
class BookingEditCustomerComponent {
    constructor(api, dialog, zone, context) {
        this.api = api;
        this.dialog = dialog;
        this.zone = zone;
        this.context = context;
        this.customer = null;
        this.has_payer = false;
        this.payer = null;
        this.vm = {
            customer: {
                name: '',
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_1__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_2__.Observable(),
                inputChange: (event) => this.customerInputChange(event),
                change: (event) => this.customerChange(event),
                focus: () => this.customerFocus(),
                restore: () => this.customerRestore(),
                reset: () => this.customerReset(),
                display: (customer) => this.customerDisplay(customer)
            },
            payer: {
                name: '',
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_1__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_2__.Observable(),
                inputChange: (event) => this.payerInputChange(event),
                change: (event) => this.payerChange(event),
                focus: () => this.payerFocus(),
                restore: () => this.payerRestore(),
                reset: () => this.payerReset(),
                display: (payer) => this.payerDisplay(payer)
            }
        };
    }
    ngOnInit() {
        /**
         * listen to the parent for changes on booking object
         */
        this.bookingInput.subscribe((booking) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            this.zone.run(() => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
                try {
                    console.log('BookingEditCustomerComponent: received change from parent, updating');
                    this.booking = booking;
                    this.has_payer = booking.has_payer_organisation;
                    if (booking.customer_id) {
                        let data = yield this.api.read("identity\\Partner", [booking.customer_id], ["id", "name", "partner_identity_id"]);
                        if (data && data.length) {
                            let customer = data[0];
                            this.customer = customer;
                            this.vm.customer.name = customer.name;
                        }
                    }
                    if (booking.payer_organisation_id) {
                        let data = yield this.api.read("identity\\Partner", [booking.payer_organisation_id], ["id", "name", "partner_identity_id"]);
                        if (data && data.length) {
                            let payer = data[0];
                            this.payer = payer;
                            this.vm.payer.name = payer.name;
                        }
                    }
                }
                catch (response) { }
            }));
        }));
        // update filtered lists, based on input clues
        this.vm.customer.filteredList = this.vm.customer.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_4__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_5__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_6__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () { return this.filterCustomers(name); })));
        this.vm.payer.filteredList = this.vm.payer.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_4__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_5__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_6__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () { return this.filterPayers(name); })));
    }
    customerChange(event) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            let customer = event.option.value;
            this.customer = customer;
            this.vm.customer.name = customer.name;
            this.bookingOutput.next({ customer_id: this.customer.id });
        });
    }
    customerInputChange(event) {
        this.vm.customer.inputClue.next(event.target.value);
    }
    customerFocus() {
        this.vm.customer.inputClue.next("");
    }
    customerDisplay(customer) {
        return customer ? customer.name : '';
    }
    customerReset() {
        setTimeout(() => {
            this.vm.customer.name = '';
        }, 100);
    }
    customerRestore() {
        if (this.customer) {
            this.vm.customer.name = this.customer.name;
        }
        else {
            this.vm.customer.name = '';
        }
    }
    payerChange(event) {
        console.log('BookingEditCustomerComponent::payerChange', event);
        // from mat-slide-toggle
        if (event && event.hasOwnProperty('checked')) {
            this.has_payer = event.checked;
            if (!this.has_payer) {
                this.bookingOutput.next({ payer_organisation_id: 0 });
            }
        }
        // from mat-autocomplete
        else if (event && event.option && event.option.value) {
            let payer = event.option.value;
            this.payer = payer;
            this.vm.payer.name = payer.name;
            this.bookingOutput.next({ payer_organisation_id: this.payer.id, has_payer_organisation: this.has_payer });
        }
    }
    payerInputChange(event) {
        this.vm.payer.inputClue.next(event.target.value);
    }
    payerFocus() {
        this.vm.payer.inputClue.next("");
    }
    payerDisplay(payer) {
        return payer ? payer.name : '';
    }
    payerReset() {
        setTimeout(() => {
            this.vm.payer.name = '';
        }, 100);
    }
    payerRestore() {
        if (this.payer) {
            this.vm.payer.name = this.payer.name;
        }
        else {
            this.vm.payer.name = '';
        }
    }
    /**
     * Request a new eQ context for selecting a payer, and relay change to self::payerChange(), if an object was created
     * #sb-booking-container is defined in booking.edit.component.html
     */
    selectPayer() {
        console.log("BookingEditCustomerComponent::selectPayer");
        let descriptor = {
            context: {
                entity: 'identity\\Partner',
                type: 'form',
                name: 'payer',
                domain: [['owner_identity_id', '=', this.customer.partner_identity_id], ['relationship', '=', 'payer']],
                mode: 'edit',
                purpose: 'create',
                target: '#sb-booking-container',
                callback: (data) => {
                    if (data && data.objects && data.objects.length) {
                        this.payerChange({ option: {
                                value: data.objects[0]
                            } });
                    }
                }
            }
        };
        this.context.change(descriptor);
    }
    createPayer() {
        const dialogRef = this.dialog.open(DialogCreatePartner, {
            width: '80vw',
            data: { owner_identity_id: this.customer.partner_identity_id, relationship: 'payer' }
        });
        dialogRef.afterClosed().subscribe(result => {
            console.log('The dialog was closed');
        });
    }
    /**
     * Search for Customers that match the name.
     *
     * @param name
     * @returns
     */
    filterCustomers(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            let filtered = [];
            try {
                let data = yield this.api.collect("identity\\Partner", [["name", "ilike", '%' + name + '%'], ["relationship", "=", "customer"]], ["id", "name"], 'name', 'asc', 0, 5);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
    filterPayers(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            let filtered = [];
            try {
                let data = yield this.api.collect("identity\\Partner", [["name", "ilike", '%' + name + '%'], ["owner_identity_id", "=", this.customer.partner_identity_id], ["relationship", "=", "payer"]], ["id", "name"], 'name', 'asc', 0, 5);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
}
BookingEditCustomerComponent.ɵfac = function BookingEditCustomerComponent_Factory(t) { return new (t || BookingEditCustomerComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialog), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_0__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__.ContextService)); };
BookingEditCustomerComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: BookingEditCustomerComponent, selectors: [["booking-edit-customer"]], inputs: { bookingInput: "bookingInput", bookingOutput: "bookingOutput" }, decls: 19, vars: 10, consts: [[1, "container"], [2, "flex", "0 1 50%"], [2, "width", "350px"], ["matInput", "", "type", "text", "placeholder", "Commencez \u00E0 taper le nom", "autocomplete", "off", 3, "matAutocomplete", "value", "keyup", "focus", "blur"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click", 4, "ngIf"], [3, "displayWith", "optionSelected"], ["customerAutocomplete", "matAutocomplete"], [4, "ngIf"], [2, "opacity", "1", 3, "align"], [3, "ngModel", "change"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click"], [3, "value", 4, "ngFor", "ngForOf"], [3, "value"], [2, "width", "50%"], ["matInput", "", "type", "text", "placeholder", "Commencez \u00E0 taper le nom", 3, "matAutocomplete", "value", "keyup", "focus", "blur"], ["payerAutocomplete", "matAutocomplete"], ["mat-button", "", 3, "click"]], template: function BookingEditCustomerComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "mat-form-field", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](3, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](4, "Client");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](5, "input", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("keyup", function BookingEditCustomerComponent_Template_input_keyup_5_listener($event) { return ctx.vm.customer.inputChange($event); })("focus", function BookingEditCustomerComponent_Template_input_focus_5_listener() { return ctx.vm.customer.focus(); })("blur", function BookingEditCustomerComponent_Template_input_blur_5_listener() { return ctx.vm.customer.restore(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](6, BookingEditCustomerComponent_button_6_Template, 3, 0, "button", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](7, "mat-autocomplete", 5, 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("optionSelected", function BookingEditCustomerComponent_Template_mat_autocomplete_optionSelected_7_listener($event) { return ctx.vm.customer.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](9, BookingEditCustomerComponent_div_9_Template, 3, 2, "div", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipe"](10, "async");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](11, "mat-hint", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](12, "span");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](13, "S\u00E9lection du client");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](14, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](15, "div");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](16, "mat-slide-toggle", 9);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("change", function BookingEditCustomerComponent_Template_mat_slide_toggle_change_16_listener($event) { return ctx.vm.payer.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](17, "R\u00E9servation pay\u00E9e par une autre organisation");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](18, BookingEditCustomerComponent_div_18_Template, 15, 8, "div", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    } if (rf & 2) {
        const _r1 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](8);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("matAutocomplete", _r1)("value", ctx.vm.customer.name);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx.vm.customer.name.length);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("displayWith", ctx.vm.customer.display);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipeBind1"](10, 8, ctx.vm.customer.filteredList));
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("align", "start");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngModel", ctx.has_payer);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx.has_payer);
    } }, directives: [_angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatFormField, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatLabel, _angular_material_input__WEBPACK_IMPORTED_MODULE_10__.MatInput, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_11__.MatAutocompleteTrigger, _angular_common__WEBPACK_IMPORTED_MODULE_12__.NgIf, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_11__.MatAutocomplete, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatHint, _angular_material_slide_toggle__WEBPACK_IMPORTED_MODULE_13__.MatSlideToggle, _angular_forms__WEBPACK_IMPORTED_MODULE_14__.NgControlStatus, _angular_forms__WEBPACK_IMPORTED_MODULE_14__.NgModel, _angular_material_button__WEBPACK_IMPORTED_MODULE_15__.MatButton, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatSuffix, _angular_material_icon__WEBPACK_IMPORTED_MODULE_16__.MatIcon, _angular_common__WEBPACK_IMPORTED_MODULE_12__.NgForOf, _angular_material_core__WEBPACK_IMPORTED_MODULE_17__.MatOption], pipes: [_angular_common__WEBPACK_IMPORTED_MODULE_12__.AsyncPipe], styles: ["[_nghost-%COMP%] {\n  width: 100%;\n  height: 100%;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: row;\n  width: 100%;\n  height: 100%;\n  margin-top: 20px;\n}\n[_nghost-%COMP%]   mat-form-field[_ngcontent-%COMP%] {\n  padding: 12px;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5jdXN0b21lci5jb21wb25lbnQuc2NzcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtFQUVFLFdBQUE7RUFDQSxZQUFBO0FBQUY7QUFFRTtFQUNFLGFBQUE7RUFDQSxtQkFBQTtFQUNBLFdBQUE7RUFDQSxZQUFBO0VBQ0EsZ0JBQUE7QUFBSjtBQUdFO0VBQ0UsYUFBQTtBQURKIiwiZmlsZSI6ImJvb2tpbmcuZWRpdC5jdXN0b21lci5jb21wb25lbnQuc2NzcyIsInNvdXJjZXNDb250ZW50IjpbIjpob3N0IHtcclxuXHJcbiAgd2lkdGg6IDEwMCU7XHJcbiAgaGVpZ2h0OiAxMDAlO1xyXG5cclxuICAuY29udGFpbmVyIHtcclxuICAgIGRpc3BsYXk6IGZsZXg7XHJcbiAgICBmbGV4LWRpcmVjdGlvbjogcm93O1xyXG4gICAgd2lkdGg6IDEwMCU7XHJcbiAgICBoZWlnaHQ6IDEwMCU7XHJcbiAgICBtYXJnaW4tdG9wOiAyMHB4O1xyXG4gIH1cclxuXHJcbiAgbWF0LWZvcm0tZmllbGQge1xyXG4gICAgcGFkZGluZzogMTJweDtcclxuICB9XHJcblxyXG4gICAgXHJcbn0iXX0= */"] });
class DialogCreatePartner {
    constructor(dialogRef, data) {
        this.dialogRef = dialogRef;
        this.data = data;
    }
    onNoClick() {
        this.dialogRef.close();
    }
}
DialogCreatePartner.ɵfac = function DialogCreatePartner_Factory(t) { return new (t || DialogCreatePartner)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogRef), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MAT_DIALOG_DATA)); };
DialogCreatePartner.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: DialogCreatePartner, selectors: [["dialog-booking-edit-customer-create-partner-dialog"]], decls: 49, vars: 1, consts: [["mat-dialog-title", ""], ["mat-dialog-content", ""], [2, "display", "flex", "flex-direction", "row"], [2, "flex", "0 1 49%", "display", "flex", "flex-direction", "column", "margin", "10px"], [2, "font-weight", "500"], ["matInput", "", "value", ""], ["mat-dialog-actions", ""], ["mat-button", "", 3, "click"], ["mat-button", "", "cdkFocusInitial", "", 3, "mat-dialog-close"]], template: function DialogCreatePartner_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "h1", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, "Nouveau partenaire");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](3, "div", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](5, "p", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](6, "Infos");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](7, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](8, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](9, "Nom l\u00E9gal");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](10, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](11, "mat-slide-toggle");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](12, "Assujetti \u00E0 la TVA ?");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](13, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](14, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](15, "Num\u00E9ro de TVA");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](16, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](17, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](18, "p", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](19, "Adresse");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](20, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](21, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](22, "Rue");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](23, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](24, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](25, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](26, "Dispatch");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](27, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](28, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](29, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](30, "Ville");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](31, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](32, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](33, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](34, "Code postal");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](35, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](36, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](37, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](38, "Pays");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](39, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](40, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](41, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](42, "Favorite Animal");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](43, "input", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](44, "div", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](45, "button", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function DialogCreatePartner_Template_button_click_45_listener() { return ctx.onNoClick(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](46, "Annuler");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](47, "button", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](48, "Cr\u00E9er");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](47);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("mat-dialog-close", ctx.data);
    } }, directives: [_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogTitle, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogContent, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatFormField, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_9__.MatLabel, _angular_material_input__WEBPACK_IMPORTED_MODULE_10__.MatInput, _angular_material_slide_toggle__WEBPACK_IMPORTED_MODULE_13__.MatSlideToggle, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogActions, _angular_material_button__WEBPACK_IMPORTED_MODULE_15__.MatButton, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogClose], encapsulation: 2 });


/***/ }),

/***/ 1351:
/*!****************************************************************************************************!*\
  !*** ./src/app/in/bookings/edit/components/booking.edit.sojourn/booking.edit.sojourn.component.ts ***!
  \****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "BookingEditSojournComponent": () => (/* binding */ BookingEditSojournComponent),
/* harmony export */   "DialogCreateContact": () => (/* binding */ DialogCreateContact)
/* harmony export */ });
/* harmony import */ var tslib__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tslib */ 4762);
/* harmony import */ var _angular_forms__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! @angular/forms */ 3679);
/* harmony import */ var _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @angular/material/dialog */ 2238);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! rxjs */ 8229);
/* harmony import */ var rxjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! rxjs */ 9165);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! rxjs/operators */ 4395);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! rxjs/operators */ 8002);
/* harmony import */ var rxjs_operators__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! rxjs/operators */ 9773);
/* harmony import */ var _angular_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @angular/core */ 7716);
/* harmony import */ var sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! sb-shared-lib */ 4725);
/* harmony import */ var _angular_material_tabs__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @angular/material/tabs */ 5939);
/* harmony import */ var _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @angular/material/form-field */ 8295);
/* harmony import */ var _angular_material_input__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @angular/material/input */ 3166);
/* harmony import */ var _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @angular/material/autocomplete */ 1554);
/* harmony import */ var _angular_common__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @angular/common */ 8583);
/* harmony import */ var _angular_cdk_text_field__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @angular/cdk/text-field */ 6109);
/* harmony import */ var _angular_material_button__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! @angular/material/button */ 1095);
/* harmony import */ var _angular_material_icon__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! @angular/material/icon */ 6627);
/* harmony import */ var _angular_material_table__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! @angular/material/table */ 2091);
/* harmony import */ var _angular_material_core__WEBPACK_IMPORTED_MODULE_19__ = __webpack_require__(/*! @angular/material/core */ 7817);
/* harmony import */ var _angular_material_select__WEBPACK_IMPORTED_MODULE_20__ = __webpack_require__(/*! @angular/material/select */ 7441);




















function BookingEditSojournComponent_button_11_Template(rf, ctx) { if (rf & 1) {
    const _r19 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "button", 29);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditSojournComponent_button_11_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r19); const ctx_r18 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r18.vm.center.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_div_14_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option", 31);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const center_r23 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("value", center_r23);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", center_r23.name, " ");
} }
function BookingEditSojournComponent_div_14_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_div_14_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](1, BookingEditSojournComponent_div_14_mat_option_1_Template, 2, 2, "mat-option", 30);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](2, BookingEditSojournComponent_div_14_mat_option_2_Template, 3, 0, "mat-option", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r20 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngForOf", list_r20);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", list_r20.length == 0);
} }
function BookingEditSojournComponent_button_23_Template(rf, ctx) { if (rf & 1) {
    const _r25 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "button", 29);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditSojournComponent_button_23_Template_button_click_0_listener() { _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r25); const ctx_r24 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r24.vm.type.reset(); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "close");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_div_26_mat_option_1_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option", 31);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const type_r29 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("value", type_r29);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", type_r29.name, " ");
} }
function BookingEditSojournComponent_div_26_mat_option_2_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-option");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "i");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](2, "pas de r\u00E9sultat");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_div_26_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](1, BookingEditSojournComponent_div_26_mat_option_1_Template, 2, 2, "mat-option", 30);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](2, BookingEditSojournComponent_div_26_mat_option_2_Template, 3, 0, "mat-option", 11);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const list_r26 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngForOf", list_r26);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", list_r26.length == 0);
} }
function BookingEditSojournComponent_th_45_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "th", 32);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, " Nom ");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_td_46_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "td", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const element_r30 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", element_r30.name, " ");
} }
function BookingEditSojournComponent_th_48_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "th", 32);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, " Type ");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_td_49_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "td", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const element_r31 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", element_r31.type, " ");
} }
function BookingEditSojournComponent_th_51_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "th", 32);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, " Email ");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_td_52_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "td", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const element_r32 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", element_r32.email, " ");
} }
function BookingEditSojournComponent_th_54_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "th", 32);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, " T\u00E9l ");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_td_55_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "td", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} if (rf & 2) {
    const element_r33 = ctx.$implicit;
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtextInterpolate1"](" ", element_r33.phone, " ");
} }
function BookingEditSojournComponent_th_57_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](0, "th", 32);
} }
function BookingEditSojournComponent_td_58_Template(rf, ctx) { if (rf & 1) {
    const _r36 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵgetCurrentView"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "td", 33);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "button", 34);
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditSojournComponent_td_58_Template_button_click_1_listener() { const restoredCtx = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵrestoreView"](_r36); const element_r34 = restoredCtx.$implicit; const ctx_r35 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵnextContext"](); return ctx_r35.vm.contacts.remove(element_r34.id); });
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "mat-icon");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](3, "delete");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function BookingEditSojournComponent_tr_59_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](0, "tr", 35);
} }
function BookingEditSojournComponent_tr_60_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](0, "tr", 36);
} }
function DialogCreateContact_mat_error_12_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-error");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, " Ce champ doit \u00EAtre rempli. ");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
function DialogCreateContact_mat_error_17_Template(rf, ctx) { if (rf & 1) {
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "mat-error");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, " Ce champ doit \u00EAtre rempli. ");
    _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
} }
class BookingEditSojournComponent {
    constructor(api, auth, dialog, zone, context) {
        this.api = api;
        this.auth = auth;
        this.dialog = dialog;
        this.zone = zone;
        this.context = context;
        this.booking = null;
        this.center = null;
        this.type = null;
        this.vm = {
            center: {
                name: '',
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_1__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_2__.Observable(),
                change: (event) => this.centerChange(event),
                inputChange: (event) => this.centerInputChange(event),
                focus: () => this.centerFocus(),
                restore: () => this.centerRestore(),
                reset: () => this.centerReset(),
                display: (center) => this.centerDisplay(center)
            },
            type: {
                name: '',
                inputClue: new rxjs__WEBPACK_IMPORTED_MODULE_1__.ReplaySubject(1),
                filteredList: new rxjs__WEBPACK_IMPORTED_MODULE_2__.Observable(),
                change: (event) => this.typeChange(event),
                inputChange: (event) => this.typeInputChange(event),
                focus: () => this.typeFocus(),
                restore: () => this.typeRestore(),
                reset: () => this.typeReset(),
                display: (type) => this.typeDisplay(type)
            },
            description: {
                value: '',
                change: (event) => this.descriptionChange(event)
            },
            contacts: {
                values: [],
                fields: ['name', 'type', 'email', 'phone', '_actions'],
                list: new rxjs__WEBPACK_IMPORTED_MODULE_1__.ReplaySubject(1),
                create: () => this.createContact(),
                remove: (id) => this.removeContact(id)
            }
        };
    }
    ngOnInit() {
        this.vm.contacts.list.subscribe((value) => {
            console.log("contact list: received new value", value);
        });
        /**
         * listen to the parent for changes on booking object
         */
        this.bookingInput.subscribe((booking) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            this.zone.run(() => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
                try {
                    console.log("BookingEditSojournComponent: received changes from parent", booking);
                    this.booking = booking;
                    if (booking.customer_id) {
                        let data = yield this.api.read("identity\\Partner", [booking.customer_id], ["id", "name", "partner_identity_id"]);
                        if (data && data.length) {
                            let customer = data[0];
                            this.customer = customer;
                        }
                    }
                    if (booking.center_id) {
                        let data = yield this.api.read("lodging\\identity\\Center", [booking.center_id], ["id", "name", "code", "organisation_id"]);
                        if (data && data.length) {
                            let center = data[0];
                            this.center = center;
                            this.vm.center.name = center.name;
                        }
                    }
                    if (booking.type_id) {
                        let data = yield this.api.read("sale\\booking\\BookingType", [booking.type_id], ["id", "name", "code"]);
                        if (data && data.length) {
                            let type = data[0];
                            this.type = type;
                            this.vm.type.name = type.name;
                        }
                    }
                    if (booking.description && booking.description.length) {
                        this.vm.description.value = booking.description;
                    }
                    if (booking.contacts_ids) {
                        let data = yield this.api.read("sale\\booking\\Contact", booking.contacts_ids, ["id", "name", "type"]);
                        console.log(booking.contacts_ids, data);
                        if (data && data.length) {
                            this.vm.contacts.values = data;
                            this.vm.contacts.list.next(data);
                        }
                    }
                }
                catch (response) { }
            }));
        }));
        // update filtered lists, based on input clues
        this.vm.center.filteredList = this.vm.center.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_4__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_5__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_6__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () { return this.filterCenters(name); })));
        this.vm.type.filteredList = this.vm.type.inputClue.pipe((0,rxjs_operators__WEBPACK_IMPORTED_MODULE_4__.debounceTime)(300), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_5__.map)((value) => (typeof value === 'string' ? value : (value == null) ? '' : value.name)), (0,rxjs_operators__WEBPACK_IMPORTED_MODULE_6__.mergeMap)((name) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () { return this.filterTypes(name); })));
    }
    centerChange(event) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            let center = event.option.value;
            this.center = center;
            this.vm.center.name = center.name;
            // relay change to parent component
            this.bookingOutput.next({ center_id: this.center.id });
        });
    }
    centerFocus() {
        this.vm.center.inputClue.next("");
    }
    centerInputChange(event) {
        this.vm.center.inputClue.next(event.target.value);
    }
    centerDisplay(center) {
        return center ? center.name : '';
    }
    centerReset() {
        setTimeout(() => {
            this.vm.center.name = '';
        }, 100);
    }
    centerRestore() {
        if (this.center) {
            this.vm.center.name = this.center.name;
        }
        else {
            this.vm.center.name = '';
        }
    }
    descriptionChange(event) {
        console.log('BookingEditCustomerComponent::descriptionChange', event);
        let description = event.target.value;
        // relay change to parent component
        this.bookingOutput.next({ description: description });
    }
    typeChange(event) {
        console.log('BookingEditCustomerComponent::typeChange', event);
        // from mat-autocomplete
        if (event && event.option && event.option.value) {
            let type = event.option.value;
            this.type = type;
            this.vm.type.name = type.name;
            // relay change to parent component
            this.bookingOutput.next({ type_id: type.id });
        }
    }
    typeInputChange(event) {
        this.vm.type.inputClue.next(event.target.value);
    }
    typeFocus() {
        this.vm.type.inputClue.next("");
    }
    typeDisplay(type) {
        return type ? type.name : '';
    }
    typeReset() {
        setTimeout(() => {
            this.vm.type.name = '';
        }, 100);
    }
    typeRestore() {
        if (this.type) {
            this.vm.type.name = this.type.name;
        }
        else {
            this.vm.type.name = '';
        }
    }
    /**
     * Permanently remove the contact (but not the identity) from the booking.
     *
     * @param id
     */
    removeContact(id) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            try {
                const identity = yield this.api.remove("sale\\booking\\Contact", [id], true);
                let index = this.vm.contacts.values.findIndex((element) => element.id == id);
                this.vm.contacts.values.splice(index, 1);
                this.vm.contacts.list.next([...this.vm.contacts.values]);
            }
            catch (response) {
                console.warn(response);
            }
        });
    }
    createContact() {
        let user = this.auth.getUser();
        let descriptor = {
            context: {
                entity: 'sale\\booking\\Contact',
                type: 'form',
                name: 'default',
                domain: [['booking_id', '=', this.booking.id], ['owner_identity_id', '=', this.customer.partner_identity_id], ['relationship', '=', 'contact'], ['type', '=', 'booking']],
                mode: 'edit',
                purpose: 'create',
                target: '#sb-booking-container',
                callback: (data) => {
                    if (data && data.objects && data.objects.length) {
                        let contact = data.objects[0];
                        let contacts_ids = this.vm.contacts.values.map((elem) => elem.id);
                        contacts_ids.push(contact.id);
                        this.bookingOutput.next({ contacts_ids: contacts_ids });
                    }
                }
            }
        };
        this.context.change(descriptor);
    }
    alternateCreateContact() {
        const dialogRef = this.dialog.open(DialogCreateContact, {
            width: '80vw',
            data: { relationship: 'contact', type: 'booking' }
        });
        dialogRef.afterClosed().subscribe((data) => (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            if (data) {
                console.log(data);
                // 1) create a new identity, and get the ID 
                try {
                    const identity = yield this.api.create("identity\\Identity", {
                        firstname: data.firstname,
                        lastname: data.lastname,
                        email: data.email,
                        phone: data.phone
                    });
                    // 2) create a partner with customer_id from booking as owner_identity_id and new identity ID as partner_identity_id
                    try {
                        let user = this.auth.getUser();
                        const contact = yield this.api.create("sale\\booking\\Contact", {
                            // owner is the organisation of current user
                            owner_identity_id: user.organisation_id,
                            partner_identity_id: identity.id,
                            booking_id: this.booking.id,
                            type: data.type
                        });
                        // update VM list of contacts
                        this.vm.contacts.values.push({
                            id: contact.id,
                            name: data.firstname + ' ' + data.lastname,
                            type: data.type,
                            email: data.email,
                            phone: data.phone
                        });
                        this.vm.contacts.list.next([...this.vm.contacts.values]);
                    }
                    catch (response) {
                        console.warn(response);
                    }
                }
                catch (response) {
                    console.warn(response);
                }
            }
        }));
    }
    /**
     * Search for Customers that match the name.
     *
     * @param name
     * @returns
     */
    filterCenters(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            let filtered = [];
            // server will return only centers visible for current User
            try {
                let data = yield this.api.collect("lodging\\identity\\Center", [["name", "ilike", '%' + name + '%']], ["id", "name"], 'name', 'asc', 0, 5);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
    filterTypes(name) {
        return (0,tslib__WEBPACK_IMPORTED_MODULE_3__.__awaiter)(this, void 0, void 0, function* () {
            let filtered = [];
            try {
                let data = yield this.api.collect("sale\\booking\\BookingType", [["name", "ilike", '%' + name + '%']], ["id", "name"], 'name', 'asc', 0, 25);
                filtered = data;
            }
            catch (response) {
                console.log(response);
            }
            return filtered;
        });
    }
}
BookingEditSojournComponent.ɵfac = function BookingEditSojournComponent_Factory(t) { return new (t || BookingEditSojournComponent)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__.ApiService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__.AuthService), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialog), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_core__WEBPACK_IMPORTED_MODULE_0__.NgZone), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](sb_shared_lib__WEBPACK_IMPORTED_MODULE_7__.ContextService)); };
BookingEditSojournComponent.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: BookingEditSojournComponent, selectors: [["booking-edit-sojourn"]], inputs: { bookingInput: "bookingInput", bookingOutput: "bookingOutput" }, decls: 61, vars: 22, consts: [[1, "container"], [2, "width", "100%"], ["label", "D\u00E9tails"], [2, "display", "flex", "flex-direction", "column"], [2, "font-weight", "500"], [2, "flex", "1", "display", "flex"], [2, "width", "350px"], ["matInput", "", "type", "text", "placeholder", "Commencez \u00E0 taper le nom", 3, "matAutocomplete", "value", "keyup", "focus", "blur"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click", 4, "ngIf"], [3, "displayWith", "optionSelected"], ["centerAutocomplete", "matAutocomplete"], [4, "ngIf"], [2, "opacity", "1", 3, "align"], [2, "width", "50%"], ["typeAutocomplete", "matAutocomplete"], [2, "flex", "1", "width", "100%", "margin-top", "20px"], ["matInput", "", "cdkTextareaAutosize", "", "cdkAutosizeMinRows", "3", "cdkAutosizeMaxRows", "20", "placeholder", "Indiquez des d\u00E9tails ou sp\u00E9cificit\u00E9s du s\u00E9jour", 3, "ngModel", "change"], ["label", "Contacts"], ["mat-mini-fab", "", "color", "primary", 2, "transform", "scale(0.65)", 3, "click"], ["mat-table", "", 2, "width", "100%", 3, "dataSource"], ["matColumnDef", "name"], ["mat-header-cell", "", 4, "matHeaderCellDef"], ["mat-cell", "", 4, "matCellDef"], ["matColumnDef", "type"], ["matColumnDef", "email"], ["matColumnDef", "phone"], ["matColumnDef", "_actions"], ["mat-header-row", "", 4, "matHeaderRowDef"], ["mat-row", "", 4, "matRowDef", "matRowDefColumns"], ["mat-button", "", "matSuffix", "", "mat-icon-button", "", "aria-label", "Clear", 3, "click"], [3, "value", 4, "ngFor", "ngForOf"], [3, "value"], ["mat-header-cell", ""], ["mat-cell", ""], ["mat-icon-button", "", "color", "primary", 3, "click"], ["mat-header-row", ""], ["mat-row", ""]], template: function BookingEditSojournComponent_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "div", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](1, "mat-tab-group", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "mat-tab", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](3, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "p", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](5, "Informations du s\u00E9jour");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](6, "div", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](7, "mat-form-field", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](8, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](9, "Centre");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](10, "input", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("keyup", function BookingEditSojournComponent_Template_input_keyup_10_listener($event) { return ctx.vm.center.inputChange($event); })("focus", function BookingEditSojournComponent_Template_input_focus_10_listener() { return ctx.vm.center.focus(); })("blur", function BookingEditSojournComponent_Template_input_blur_10_listener() { return ctx.vm.center.restore(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](11, BookingEditSojournComponent_button_11_Template, 3, 0, "button", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](12, "mat-autocomplete", 9, 10);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("optionSelected", function BookingEditSojournComponent_Template_mat_autocomplete_optionSelected_12_listener($event) { return ctx.vm.center.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](14, BookingEditSojournComponent_div_14_Template, 3, 2, "div", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipe"](15, "async");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](16, "mat-hint", 12);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](17, "span");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](18, "S\u00E9lection du centre");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](19, "mat-form-field", 13);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](20, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](21, "Type de r\u00E9servation");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](22, "input", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("keyup", function BookingEditSojournComponent_Template_input_keyup_22_listener($event) { return ctx.vm.type.inputChange($event); })("focus", function BookingEditSojournComponent_Template_input_focus_22_listener() { return ctx.vm.type.focus(); })("blur", function BookingEditSojournComponent_Template_input_blur_22_listener() { return ctx.vm.type.restore(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](23, BookingEditSojournComponent_button_23_Template, 3, 0, "button", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](24, "mat-autocomplete", 9, 14);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("optionSelected", function BookingEditSojournComponent_Template_mat_autocomplete_optionSelected_24_listener($event) { return ctx.vm.type.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](26, BookingEditSojournComponent_div_26_Template, 3, 2, "div", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipe"](27, "async");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](28, "mat-hint", 12);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](29, "span");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](30, "Type de s\u00E9jour de la r\u00E9servation");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](31, "div", 15);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](32, "mat-form-field", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](33, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](34, "Description");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](35, "textarea", 16);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("change", function BookingEditSojournComponent_Template_textarea_change_35_listener($event) { return ctx.vm.description.change($event); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](36, "mat-tab", 17);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](37, "p", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](38, "Liste des contacts ");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](39, "button", 18);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function BookingEditSojournComponent_Template_button_click_39_listener() { return ctx.vm.contacts.create(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](40, "mat-icon");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](41, "add");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](42, "table", 19);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipe"](43, "async");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerStart"](44, 20);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](45, BookingEditSojournComponent_th_45_Template, 2, 0, "th", 21);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](46, BookingEditSojournComponent_td_46_Template, 2, 1, "td", 22);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerStart"](47, 23);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](48, BookingEditSojournComponent_th_48_Template, 2, 0, "th", 21);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](49, BookingEditSojournComponent_td_49_Template, 2, 1, "td", 22);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerStart"](50, 24);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](51, BookingEditSojournComponent_th_51_Template, 2, 0, "th", 21);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](52, BookingEditSojournComponent_td_52_Template, 2, 1, "td", 22);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerStart"](53, 25);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](54, BookingEditSojournComponent_th_54_Template, 2, 0, "th", 21);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](55, BookingEditSojournComponent_td_55_Template, 2, 1, "td", 22);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerStart"](56, 26);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](57, BookingEditSojournComponent_th_57_Template, 1, 0, "th", 21);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](58, BookingEditSojournComponent_td_58_Template, 4, 0, "td", 22);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementContainerEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](59, BookingEditSojournComponent_tr_59_Template, 1, 0, "tr", 27);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](60, BookingEditSojournComponent_tr_60_Template, 1, 0, "tr", 28);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    } if (rf & 2) {
        const _r1 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](13);
        const _r4 = _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵreference"](25);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](10);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("matAutocomplete", _r1)("value", ctx.vm.center.name);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx.vm.center.name.length);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("displayWith", ctx.vm.center.display);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipeBind1"](15, 16, ctx.vm.center.filteredList));
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("align", "start");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("matAutocomplete", _r4)("value", ctx.vm.type.name);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx.vm.type.name.length);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("displayWith", ctx.vm.type.display);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipeBind1"](27, 18, ctx.vm.type.filteredList));
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("align", "start");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngModel", ctx.vm.description.value);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("dataSource", _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵpipeBind1"](43, 20, ctx.vm.contacts.list));
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](17);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("matHeaderRowDef", ctx.vm.contacts.fields);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("matRowDefColumns", ctx.vm.contacts.fields);
    } }, directives: [_angular_material_tabs__WEBPACK_IMPORTED_MODULE_9__.MatTabGroup, _angular_material_tabs__WEBPACK_IMPORTED_MODULE_9__.MatTab, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__.MatFormField, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__.MatLabel, _angular_material_input__WEBPACK_IMPORTED_MODULE_11__.MatInput, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_12__.MatAutocompleteTrigger, _angular_common__WEBPACK_IMPORTED_MODULE_13__.NgIf, _angular_material_autocomplete__WEBPACK_IMPORTED_MODULE_12__.MatAutocomplete, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__.MatHint, _angular_cdk_text_field__WEBPACK_IMPORTED_MODULE_14__.CdkTextareaAutosize, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.DefaultValueAccessor, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.NgControlStatus, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.NgModel, _angular_material_button__WEBPACK_IMPORTED_MODULE_16__.MatButton, _angular_material_icon__WEBPACK_IMPORTED_MODULE_17__.MatIcon, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatTable, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatColumnDef, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatHeaderCellDef, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatCellDef, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatHeaderRowDef, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatRowDef, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__.MatSuffix, _angular_common__WEBPACK_IMPORTED_MODULE_13__.NgForOf, _angular_material_core__WEBPACK_IMPORTED_MODULE_19__.MatOption, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatHeaderCell, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatCell, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatHeaderRow, _angular_material_table__WEBPACK_IMPORTED_MODULE_18__.MatRow], pipes: [_angular_common__WEBPACK_IMPORTED_MODULE_13__.AsyncPipe], styles: ["[_nghost-%COMP%] {\n  width: 100%;\n  height: 100%;\n}\n[_nghost-%COMP%]   .container[_ngcontent-%COMP%] {\n  display: flex;\n  height: 100%;\n  width: 100%;\n}\n[_nghost-%COMP%]   mat-form-field[_ngcontent-%COMP%] {\n  padding: 12px;\n}\n[_nghost-%COMP%]   .mat-table[_ngcontent-%COMP%]   .mat-column-_actions[_ngcontent-%COMP%] {\n  width: 40px !important;\n  padding: 0;\n}\n  mat-form-field.mat-focused textarea {\n  outline: solid 1px lightgrey;\n}\n/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJvb2tpbmcuZWRpdC5zb2pvdXJuLmNvbXBvbmVudC5zY3NzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0VBRUUsV0FBQTtFQUNBLFlBQUE7QUFBRjtBQUdFO0VBQ0UsYUFBQTtFQUNBLFlBQUE7RUFDQSxXQUFBO0FBREo7QUFJRTtFQUNFLGFBQUE7QUFGSjtBQU1JO0VBQ0ksc0JBQUE7RUFDQSxVQUFBO0FBSlI7QUFVQTtFQUNFLDRCQUFBO0FBUEYiLCJmaWxlIjoiYm9va2luZy5lZGl0LnNvam91cm4uY29tcG9uZW50LnNjc3MiLCJzb3VyY2VzQ29udGVudCI6WyI6aG9zdCB7XHJcblxyXG4gIHdpZHRoOiAxMDAlO1xyXG4gIGhlaWdodDogMTAwJTtcclxuXHJcblxyXG4gIC5jb250YWluZXIge1xyXG4gICAgZGlzcGxheTogZmxleDtcclxuICAgIGhlaWdodDogMTAwJTtcclxuICAgIHdpZHRoOiAxMDAlO1xyXG4gIH1cclxuXHJcbiAgbWF0LWZvcm0tZmllbGQge1xyXG4gICAgcGFkZGluZzogMTJweDtcclxuICB9XHJcblxyXG4gIC5tYXQtdGFibGUge1xyXG4gICAgLm1hdC1jb2x1bW4tX2FjdGlvbnMge1xyXG4gICAgICAgIHdpZHRoOiA0MHB4ICFpbXBvcnRhbnQ7XHJcbiAgICAgICAgcGFkZGluZzogMDtcclxuICAgIH1cclxuICB9XHJcbiAgICBcclxufVxyXG5cclxuOjpuZy1kZWVwIG1hdC1mb3JtLWZpZWxkLm1hdC1mb2N1c2VkIHRleHRhcmVhIHtcclxuICBvdXRsaW5lOiBzb2xpZCAxcHggbGlnaHRncmV5O1xyXG59ICAiXX0= */"] });
class DialogCreateContact {
    constructor(dialogRef, formBuilder, data) {
        this.dialogRef = dialogRef;
        this.formBuilder = formBuilder;
        this.data = data;
        this.form = this.formBuilder.group({
            firstname: [this.data ? this.data.firstname : '', _angular_forms__WEBPACK_IMPORTED_MODULE_15__.Validators.required],
            lastname: [this.data ? this.data.lastname : '', _angular_forms__WEBPACK_IMPORTED_MODULE_15__.Validators.required],
            type: [this.data ? this.data.type : 'booking', _angular_forms__WEBPACK_IMPORTED_MODULE_15__.Validators.required],
            email: [this.data ? this.data.email : '', [_angular_forms__WEBPACK_IMPORTED_MODULE_15__.Validators.required, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.Validators.email]],
            phone: [this.data ? this.data.phone : ''],
        });
    }
    get f() { return this.form.controls; }
    markFormGroupTouched(formGroup) {
        Object.values(formGroup.controls).forEach((control) => {
            control.markAsTouched();
            if (control.controls) {
                this.markFormGroupTouched(control);
            }
        });
    }
    onCancel() {
        this.dialogRef.close();
    }
    onSubmit() {
        // stop here if form is invalid
        if (this.form.invalid) {
            return this.markFormGroupTouched(this.form);
        }
        this.dialogRef.close({
            firstname: this.form.value.firstname,
            lastname: this.form.value.lastname,
            type: this.form.value.type,
            email: this.form.value.email,
            phone: this.form.value.phone
        });
    }
}
DialogCreateContact.ɵfac = function DialogCreateContact_Factory(t) { return new (t || DialogCreateContact)(_angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogRef), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_forms__WEBPACK_IMPORTED_MODULE_15__.FormBuilder), _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdirectiveInject"](_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MAT_DIALOG_DATA)); };
DialogCreateContact.ɵcmp = /*@__PURE__*/ _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵdefineComponent"]({ type: DialogCreateContact, selectors: [["dialog-booking-edit-customer-create-contact-dialog"]], decls: 46, vars: 3, consts: [["mat-dialog-title", ""], ["mat-dialog-content", ""], [3, "formGroup", "ngSubmit"], [2, "display", "flex", "flex-direction", "row"], [2, "flex", "0 1 49%", "display", "flex", "flex-direction", "column", "margin", "10px"], [2, "font-weight", "500"], ["matInput", "", "formControlName", "firstname"], [4, "ngIf"], ["matInput", "", "formControlName", "lastname"], ["formControlName", "type"], ["value", "booking"], ["value", "invoice"], ["value", "contract"], ["value", "sojourn"], ["matInput", "", "formControlName", "email"], ["matInput", "", "formControlName", "phone"], ["mat-dialog-actions", "", 2, "justify-content", "flex-end"], ["mat-button", "", 3, "click"], ["mat-button", "", "cdkFocusInitial", "", 3, "click"]], template: function DialogCreateContact_Template(rf, ctx) { if (rf & 1) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](0, "h1", 0);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](1, "Nouveau contact pour la r\u00E9servation");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](2, "div", 1);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](3, "form", 2);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("ngSubmit", function DialogCreateContact_Template_form_ngSubmit_3_listener() { return ctx.onSubmit(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](4, "div", 3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](5, "div", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](6, "p", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](7, "Infos");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](8, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](9, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](10, "Pr\u00E9nom");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](11, "input", 6);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](12, DialogCreateContact_mat_error_12_Template, 2, 0, "mat-error", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](13, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](14, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](15, "Nom");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](16, "input", 8);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtemplate"](17, DialogCreateContact_mat_error_17_Template, 2, 0, "mat-error", 7);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](18, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](19, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](20, "Type de contact");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](21, "mat-select", 9);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](22, "mat-option", 10);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](23, "r\u00E9servation");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](24, "mat-option", 11);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](25, "facturation");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](26, "mat-option", 12);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](27, "contrats");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](28, "mat-option", 13);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](29, "s\u00E9jour");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](30, "div", 4);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](31, "p", 5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](32, "Coordonn\u00E9es");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](33, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](34, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](35, "Email");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](36, "input", 14);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](37, "mat-form-field");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](38, "mat-label");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](39, "Phone");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelement"](40, "input", 15);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](41, "div", 16);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](42, "button", 17);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function DialogCreateContact_Template_button_click_42_listener() { return ctx.onCancel(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](43, "Annuler");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementStart"](44, "button", 18);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵlistener"]("click", function DialogCreateContact_Template_button_click_44_listener() { return ctx.onSubmit(); });
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵtext"](45, "Ok");
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵelementEnd"]();
    } if (rf & 2) {
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](3);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("formGroup", ctx.form);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](9);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx.f.firstname.errors == null ? null : ctx.f.firstname.errors.required);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵadvance"](5);
        _angular_core__WEBPACK_IMPORTED_MODULE_0__["ɵɵproperty"]("ngIf", ctx.f.lastname.errors == null ? null : ctx.f.lastname.errors.required);
    } }, directives: [_angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogTitle, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogContent, _angular_forms__WEBPACK_IMPORTED_MODULE_15__["ɵNgNoValidate"], _angular_forms__WEBPACK_IMPORTED_MODULE_15__.NgControlStatusGroup, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.FormGroupDirective, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__.MatFormField, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__.MatLabel, _angular_material_input__WEBPACK_IMPORTED_MODULE_11__.MatInput, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.DefaultValueAccessor, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.NgControlStatus, _angular_forms__WEBPACK_IMPORTED_MODULE_15__.FormControlName, _angular_common__WEBPACK_IMPORTED_MODULE_13__.NgIf, _angular_material_select__WEBPACK_IMPORTED_MODULE_20__.MatSelect, _angular_material_core__WEBPACK_IMPORTED_MODULE_19__.MatOption, _angular_material_dialog__WEBPACK_IMPORTED_MODULE_8__.MatDialogActions, _angular_material_button__WEBPACK_IMPORTED_MODULE_16__.MatButton, _angular_material_form_field__WEBPACK_IMPORTED_MODULE_10__.MatError], encapsulation: 2 });


/***/ })

}]);
//# sourceMappingURL=src_app_in_bookings_booking_module_ts.js.map