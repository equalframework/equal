/**
 * Model definitions
 */


export const customer_types = [
  { id: 1, code: 'I', name: 'Particulier (personne physique)' },
  { id: 2, code: 'SE', name:'Indépendant' },
  { id: 3, code: 'C', name: 'Société' },
  { id: 4, code: 'NP', name: 'Association ou École (asbl, association de fait ou autre)' },
  { id: 5, code: 'PA', name: 'Administration publique' }
];


export const rate_classes = [
  { id: 1, name: "Institutions reconnues CWB", description: "" },
  { id: 2, name: "Autres associations sociales", description: "" },
  { id: 3, name: "Travailleurs Kaleo", description: "" },
  { id: 4, name: "Grand public", description: "" },
  { id: 5, name: "Ecoles primaires et secondaires", description: "" },
  { id: 6, name: "TO / Partenaires", description: "" },
  { id: 7, name: "Ecoles maternelles", description: "" }
];


export const customer_natures = [
  { id: 1, code: "AA", name: "Ancien administrateur CBTJ/Kaleo", rate_class_id: {id: 3, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 2, code: "AC", name: "Administration publique", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 5, code:'PA', name:''} },
  { id: 3, code: "AD", name: "Administrateur CBTJ/Kaleo", rate_class_id: {id: 3, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 4, code: "AL", name: "ATL (Accueil Temps Libre)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 5, code: "AM", name: "Groupe d\'amis", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 6, code: "AN", name: "Animateur", rate_class_id: {id: 3, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 7, code: "AR", name: "Association (sociale divers)", rate_class_id: {id: 2, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 8, code: "AS", name: "Association (grand public)", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 9, code: "AT", name: "Ancien travailleur CBTJ/Kaleo", rate_class_id: {id: 3, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 10, code: "CC", name: "Culture - Centre culturel ou Association culturelle)", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 11, code: "CE", name: "CEC (Coopération par l'Education et la Culture)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 12, code: "CH", name: "Chorale & Aca de Musique", rate_class_id: {id: 2, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 13, code: "CP", name: "CPAS", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 5, code:'PA', name:''} },
  { id: 14, code: "CS", name: "Club sportif / Fédération", rate_class_id: {id: 2, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 15, code: "EC", name: "Ecole (non précisée)", rate_class_id: {id: 5, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 16, code: "ED", name: "AEC (Association d\'Education Permanente)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 17, code: "EG", name: "Eglise - groupe religieux", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 18, code: "EM", name: "Ecole maternelle", rate_class_id: {id: 7, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 19, code: "EN", name: "Entreprise (société privée)", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 3, code:'C', name:''} },
  { id: 20, code: "EP", name: "Ecole primaire", rate_class_id: {id: 5, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 21, code: "ES", name: "Ecole secondaire", rate_class_id: {id: 5, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 22, code: "FA", name: "Famille", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 23, code: "FM", name: "Fanfare-groupe musique", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 24, code: "GA", name: "Gérant gîte Auberge", rate_class_id: {id: 3, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 25, code: "GG", name: "Gérant gîte de Groupes", rate_class_id: {id: 3, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 26, code: "HA", name: "Home pour adultes", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 27, code: "HE", name: "Haute Ecole", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 28, code: "HO", name: "Home pour enfants (Maison d'Accueil)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
/* @see 'individuel' { id: 29, code: "IB", name: "Individuel BOOKING", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} }, */
  { id: 30, code: "IN", name: "Individuel (particulier)", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 31, code: "IP", name: "IPPJ (Institution publique de protection de la jeunesse)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
/* @see 'individuel' { id: 32, code: "IR", name: "Individuel Reservit", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} }, */
  { id: 33, code: "JE", name: "Maison de Jeunes Flandres (Jeugdhuis)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 34, code: "M3", name: "Maison de Quartier", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 35, code: "MJ", name: "Maison de Jeunes Wallonie", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 36, code: "MO", name: "AMO (Aide en Milieu Ouvert)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 37, code: "MU", name: "Mutuelle", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 38, code: "OF", name: "Officiels (délégation politique)", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'PA', name:''} },
  { id: 50, code: "PP", name: "Parti politque", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },  
  { id: 39, code: "OJ", name: "Organisation de jeunesse", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 40, code: "PR", name: "Presse", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 3, code:'C', name:''} },
  { id: 41, code: "SC", name: "Mouvement de Jeunesse", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 42, code: "SI", name: "Syndicat Initiative - Maison du Tourisme", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 43, code: "sj", name: "SAJ/SPJ (Service de l\'aide à la jeunesse)", rate_class_id: {id: 1, name: '', description: ''}, customer_type_id: {id: 5, code:'PA', name:''} },
/* @see 'entreprise' { id: 44, code: "SO", name: "Société privée", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 3, code:'C', name:''} },*/
  { id: 45, code: "SP", name: "Ecole spéciale (handicap)", rate_class_id: {id: 5, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 46, code: "TC", name: "Travailleur CBTJ/Kaleo", rate_class_id: {id: 3, name: '', description: ''}, customer_type_id: {id: 1, code:'I', name:''} },
  { id: 47, code: "TO", name: "Tour opérateur", rate_class_id: {id: 6, name: '', description: ''}, customer_type_id: {id: 3, code:'C', name:''} },
  { id: 48, code: "UC", name: "Club Estudiantin", rate_class_id: {id: 4, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} },
  { id: 49, code: "US", name: "Université - Enseignement Supérieur", rate_class_id: {id: 2, name: '', description: ''}, customer_type_id: {id: 4, code:'NP', name:''} }
];

export class CenterClass {
  id: number = 1;
  name: string = '';
  description: string = '';
}


export class RateClassClass {
  id: number = 1;
  name: string = '';
  description: string = '';
}

export class CustomerNatureClass {
  id: number = 1;
  code: string = '';
  name: string = '';
  rate_class_id: RateClassClass = new RateClassClass();
  customer_type_id: CustomerTypeClass = new CustomerTypeClass();
}

export class CustomerTypeClass {
  id: number = 1;
  code: string = '';
  name: string = '';
}

export class PriceClass {
  id: number = 1;
  price: number = 0;
  type: string ='direct';
}

export class VatRuleClass {
  id: number = 1;
  rate: number = 0.0;
}

export class AccountingRuleClass {
  id: number = 1;
  vat_rule_id: VatRuleClass = new VatRuleClass();
}

export class ProductModelClass {
  id: number;
  qty_accounting_method: string = 'unit';
  rental_unit_assignement: string = 'category';
  capacity: number = 0;
  duration: number = 0;
  type: string = 'service';
  service_type: string = 'simple';
  is_pack: boolean = false;
  has_own_price: boolean = true;
  schedule_offset: number = 0;
  selling_accouting_rule: AccountingRuleClass = new AccountingRuleClass();
  rental_unit_category_id: RentalUnitCategoryClass = new RentalUnitCategoryClass();
  rental_unit_id: RentalUnitClass = new RentalUnitClass();
}

export class ProductClass {
  id: number = 0;
  sku: string = '';
  name: string = '';
  product_model_id: ProductModelClass = new ProductModelClass();
  pack_lines_ids: any[];
}

export class BookingLineClass {
  id: number = Math.ceil(Math.random() * 1000);
  product_id: ProductClass = new ProductClass();
  price_id: PriceClass  = new PriceClass();
  qty: number = 1;
  freebies: number = 0;
  discounts: any[] = [];
}

export class DiscountClass {
  type: string = 'amount';
  value: number = 0;

}

export class ConsumptionClass {
  id: number = Math.ceil(Math.random() * 1000);
  date: Date;
  product_id: ProductClass = new ProductClass();
  booking_line_id: BookingLineClass = new BookingLineClass();
  disclaimed: boolean = false;
  rental_unit_id: RentalUnitClass = new RentalUnitClass();  
}


export class RentalUnitClass {
  id: number = 1;
  name: string = '';
  description: string = '';
  type: string = '';
  center_id: number = 1;
  capacity: number = 1;
  has_children: boolean = false;
  parent_id: number = 0;
  category_id: RentalUnitCategoryClass = new RentalUnitCategoryClass();
}

export class RentalUnitCategoryClass {
  id: number = 1;
  name: string = '';
}

export class CompositionItemClass {
  id: number = Math.ceil(Math.random() * 1000);  
  firstname: string = 'prénom';
  lastname: string = 'nom';
  gender: string = 'M'; // M, F
  date_of_birth: Date = new Date(Date.now() - (27*365*24*60*60*1000));  
  handicap: string = 'none'; // none, slight, moderate, severe
  underprivileged: boolean = false;
}



