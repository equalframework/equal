import { Component, Input, OnInit, AfterViewInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { SettingService } from 'src/app/settingService';

@Component({
  selector: 'app-widget-input',
  templateUrl: './widget-input.component.html',
  styleUrls: ['./widget-input.component.scss']
})
export class WidgetInputComponent implements OnInit, AfterViewInit {
  @Input() setting: any;

  public settingValue: any;
  public settingName: any;

  public formControl = new FormControl();

  public showResetButton = false;
  public showSubmitButton = false;

  public focusState: any;

  constructor(public save: SettingService) { }

  ngOnInit(): void {
  }

  ngAfterViewInit() {
      if(this.setting.setting_values_ids && this.setting.setting_values_ids.length) {
        this.settingValue = this.setting.setting_values_ids[0].value;
        this.settingName = this.setting.code;
        // preset the value of formControl
        this.formControl.setValue(this.settingValue);
      }
  }

  /**
   *  Clear the input field.
   *
   */
  public onReset(event:any) {
    console.log('WidgetFormComponent::onReset');
    // #memo - we use 'mousedown' instead of 'click' to prevent input from losing the focus
    event.stopPropagation();
    event.preventDefault();
    this.formControl.reset();
    // hide buttons
    this.showResetButton = false;
    this.showSubmitButton = false;
  }

  /**
   * Input has been modified : show submit button.
   */
  public onChange() {
    console.log('WidgetFormComponent::onChange');
    this.showSubmitButton = true;
    this.showResetButton = true;
  }

  public onFocus(){
    console.log('WidgetFormComponent::onFocus');
    this.showResetButton = true;
  }

  public onBlur(){
    console.log('WidgetFormComponent::onBlur');
    // hide buttons
    this.showResetButton = false;
    this.showSubmitButton = false;
    // reset input formControl
    this.formControl.setValue(this.settingValue);
  }

  public onSubmit(event:any) {
    console.log('WidgetFormComponent::onSubmit');
    // #memo - we use 'mousedown' instead of 'click' to prevent input from losing the focus
    event.stopPropagation();
    event.preventDefault();

    let newValue = this.formControl.value;
    let oldValue = this.settingValue;

    if (newValue != oldValue) {
      this.settingValue = newValue;
      this.showSubmitButton = false;
      this.showResetButton = false;
      this.save.toQueue(this.setting.setting_values_ids[0].id, { newValue: newValue, oldValue: oldValue }).subscribe( (action) => {
        if (action == 'undo') {
          this.formControl.setValue(oldValue);
          this.settingValue = oldValue;
        }
      });
    }
  }
}
