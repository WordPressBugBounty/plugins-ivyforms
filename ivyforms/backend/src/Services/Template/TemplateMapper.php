<?php

declare(strict_types=1);

namespace IvyForms\Services\Template;

/**
 * Class TemplateMapper
 *
 * Maps template IDs to their corresponding template classes
 *
 * @package IvyForms\Services\Template
 */
class TemplateMapper
{
    /**
     * Template ID to class mapping
     *
     * @var array<string, string>
     */
    private static array $templateMap = [
        'contact_form' => ContactFormTemplate::class,
        'blank_form' => BlankFormTemplate::class,
        'wedding_rsvp' => WeddingRsvpTemplate::class,
        'anniversary_dinner_reservation_form' => AnniversaryDinnerReservationFormTemplate::class,
        'birthday_dinner_reservation_form' => BirthdayDinnerReservationFormTemplate::class,
        'private_dining_reservation_form' => PrivateDiningReservationFormTemplate::class,
        'craft_vendor_registration_form' => CraftVendorRegistrationFormTemplate::class,
        'food_vendor_registration_form' => FoodVendorRegistrationFormTemplate::class,
        'fitness_class_registration_form' => FitnessClassRegistrationFormTemplate::class,
        'yoga_class_registration_form' => YogaClassRegistrationFormTemplate::class,
        'cooking_class_registration_form' => CookingClassRegistrationFormTemplate::class,
        'conference_room_booking_form' => ConferenceRoomBookingFormTemplate::class,
        'product_order_form' => ProductOrderFormTemplate::class,
        'b2b_product_order_form' => B2bProductOrderFormTemplate::class,
        'corporate_office_supplies_procurement_form' => CorporateOfficeSuppliesProcurementFormTemplate::class,
        'product_customization_form' => ProductCustomizationFormTemplate::class,
        'technology_innovation_summit_registration_form' => TechnologyInnovationSummitRegistrationFormTemplate::class,
        'healthcare_medical_conference_registration_form' => HealthcareMedicalConferenceRegistrationFormTemplate::class,
        'marketing_growth_leadership_conference_registration_form'
            => MarketingGrowthLeadershipConferenceRegistrationFormTemplate::class,
        'financial_services_fintech_leadership_conference_registration_form'
            => FinancialServicesFintechLeadershipConferenceRegistrationFormTemplate::class,
        'education_academic_research_conference_registration_form'
            => EducationAcademicResearchConferenceRegistrationFormTemplate::class,
        'cybersecurity_data_protection_conference_registration_form'
            => CybersecurityDataProtectionConferenceRegistrationFormTemplate::class,
        'technology_conference_event_feedback_form' => TechnologyConferenceEventFeedbackFormTemplate::class,
        'healthcare_seminar_medical_event_feedback_form' => HealthcareSeminarMedicalEventFeedbackFormTemplate::class,
        'marketing_networking_event_feedback_form' => MarketingNetworkingEventFeedbackFormTemplate::class,
        'corporate_employee_training_feedback_form' => CorporateEmployeeTrainingFeedbackFormTemplate::class,
        'technical_skills_workshop_feedback_form' => TechnicalSkillsWorkshopFeedbackFormTemplate::class,
        'online_course_elearning_feedback_form' => OnlineCourseElearningFeedbackFormTemplate::class,
        'medical_specialist_appointment_booking_form' => MedicalSpecialistAppointmentBookingFormTemplate::class,
        'beauty_salon_personal_care_appointment_form' => BeautySalonPersonalCareAppointmentFormTemplate::class,
        'business_consultation_strategy_session_booking_form'
            => BusinessConsultationStrategySessionBookingFormTemplate::class,
        'event_registration' => EventRegistrationTemplate::class,
        'event_tshirt_order_form' => EventTShirtOrderFormTemplate::class,
        'custom_tshirt_order_form' => CustomTShirtOrderFormTemplate::class,
        'custom_cake_order_form' => CustomCakeOrderFormTemplate::class,
        'wedding_cake_order_form' => WeddingCakeOrderFormTemplate::class,
        'birthday_cake_order_form' => BirthdayCakeOrderFormTemplate::class,
        'doctor_appointment_booking_form' => DoctorAppointmentBookingFormTemplate::class,
        'dinner_reservation_form' => DinnerReservationFormTemplate::class,
        'hair_salon_appointment_form' => HairSalonAppointmentFormTemplate::class,
        'nail_salon_appointment_form' => NailSalonAppointmentFormTemplate::class,
        'spa_appointment_booking_form' => SpaAppointmentBookingFormTemplate::class,
        'consultation_appointment_form' => ConsultationAppointmentFormTemplate::class,
        'birthday_tshirt_order_form' => BirthdayTShirtOrderFormTemplate::class,
        'corporate_tshirt_order_form' => CorporateTShirtOrderFormTemplate::class,
        'residential_construction_change_form' => ResidentialConstructionChangeFormTemplate::class,
        'festival_waiver_form' => FestivalWaiverFormTemplate::class,
        'charity_run_waiver_form' => CharityRunWaiverFormTemplate::class,
        'volunteer_event_waiver_form' => VolunteerEventWaiverFormTemplate::class,
        'community_event_waiver_form' => CommunityEventWaiverFormTemplate::class,
        'school_trip_waiver_form' => SchoolTripWaiverFormTemplate::class,
        'yoga_class_waiver_form' => YogaClassWaiverFormTemplate::class,
        'dance_class_waiver_form' => DanceClassWaiverFormTemplate::class,
        'hiking_trip_waiver_form' => HikingTripWaiverFormTemplate::class,
        'gym_waiver_form' => GymWaiverFormTemplate::class,
        'sports_event_waiver_form' => SportsEventWaiverFormTemplate::class,
        'universal_construction_change_form' => UniversalConstructionChangeFormTemplate::class,
        'maintenance_work_order_form' => MaintenanceWorkOrderFormTemplate::class,
        'vacation_leave_request_form' => VacationLeaveRequestFormTemplate::class,
        'sick_leave_request_form' => SickLeaveRequestFormTemplate::class,
        'annual_leave_request_form' => AnnualLeaveRequestFormTemplate::class,
        'maternity_leave_request_form' => MaternityLeaveRequestFormTemplate::class,
        'conference_registration_form' => ConferenceRegistrationFormTemplate::class,
        'webinar_registration_form' => WebinarRegistrationFormTemplate::class,
        'corporate_photo_consent_form' => CorporatePhotoConsentFormTemplate::class,
        'conference_photo_consent_form' => ConferencePhotoConsentFormTemplate::class,
        'sports_team_photo_consent_form' => SportsTeamPhotoConsentFormTemplate::class,
        'school_photo_consent_form' => SchoolPhotoConsentFormTemplate::class,
        'event_photo_consent_form' => EventPhotoConsentFormTemplate::class,
        'photo_video_consent_form' => PhotoVideoConsentFormTemplate::class,
        'workshop_registration_form' => WorkshopRegistrationFormTemplate::class,
        'charity_event_registration_form' => CharityEventRegistrationFormTemplate::class,
        'baby_shower_cake_order_form' => BabyShowerCakeOrderFormTemplate::class,
        'general_medical_consent_form' => GeneralMedicalConsentFormTemplate::class,
        'surgery_informed_consent_form' => SurgeryInformedConsentFormTemplate::class,
        'general_change_order_form' => GeneralChangeOrderFormTemplate::class,
        'marketing_conference_registration_form' => MarketingConferenceRegistrationFormTemplate::class,
        'medical_conference_registration_form' => MedicalConferenceRegistrationFormTemplate::class,
        'tech_conference_registration_form' => TechConferenceRegistrationFormTemplate::class,
        'blood_transfusion_consent_form' => BloodTransfusionConsentFormTemplate::class,
        'vaccination_consent_form' => VaccinationConsentFormTemplate::class,
        'anesthesia_consent_form' => AnesthesiaConsentFormTemplate::class,
        'garden_wedding_rsvp_form' => GardenWeddingRSVPFormTemplate::class,
        'beach_wedding_rsvp_form' => BeachWeddingRSVPFormTemplate::class,
        'destination_wedding_rsvp_form' => DestinationWeddingRSVPFormTemplate::class,
        'traditional_wedding_rsvp_form' => TraditionalWeddingRSVPFormTemplate::class,
        'wedding_registration_form' => WeddingRegistrationFormTemplate::class,
        'software_purchase_order_form' => SoftwarePurchaseOrderFormTemplate::class,
        'office_supplies_purchase_order_form' => OfficeSuppliesPurchaseOrderFormTemplate::class,
        'equipment_purchase_order_form' => EquipmentPurchaseOrderFormTemplate::class,
        'material_purchase_order_form' => MaterialPurchaseOrderFormTemplate::class,
        'education_conference_registration_form' => EducationConferenceRegistrationFormTemplate::class,
        'repair_work_order_form' => RepairWorkOrderFormTemplate::class,
        'installation_work_order_form' => InstallationWorkOrderFormTemplate::class,
        'team_tshirt_order_form' => TeamTshirtOrderFormTemplate::class,
        'kitchen_upgrade_change_form' => KitchenUpgradeChangeFormTemplate::class,
        'commercial_construction_change_form' => CommercialConstructionChangeFormTemplate::class,
        'hr_interview_feedback_form' => HRInterviewFeedbackFormTemplate::class,
        'technical_role_interview_feedback_form' => TechnicalRoleInterviewFeedbackFormTemplate::class,
        'candidate_evaluation_form' => CandidateEvaluationFormTemplate::class,
        'technical_interview_feedback_form' => TechnicalInterviewFeedbackFormTemplate::class,
        'course_content_feedback_form' => CourseContentFeedbackFormTemplate::class,
        'trainer_feedback_form' => TrainerFeedbackFormTemplate::class,
        'online_training_feedback_form' => OnlineTrainingFeedbackFormTemplate::class,
        'employee_training_feedback_form' => EmployeeTrainingFeedbackFormTemplate::class,
        'client_loyalty_testimonial_readiness_form' => ClientLoyaltyTestimonialReadinessFormTemplate::class,
        'client_relationship_communication_feedback' => ClientRelationshipCommunicationFeedbackTemplate::class,
        'general_client_feedback_form' => GeneralClientFeedbackFormTemplate::class,
        'service_specific_client_feedback_form' => ServiceSpecificClientFeedbackFormTemplate::class,
        'ongoing_client_feedback_form' => OngoingClientFeedbackFormTemplate::class,
        'training_event_feedback_form' => TrainingEventFeedbackFormTemplate::class,
        'networking_event_feedback_form' => NetworkingEventFeedbackFormTemplate::class,
        'workshop_feedback_form' => WorkshopFeedbackFormTemplate::class,
        'customer_loyalty_recommendation_form' => CustomerLoyaltyRecommendationFormTemplate::class,
        'webinar_feedback_form' => WebinarFeedbackFormTemplate::class,
        'conference_feedback_form' => ConferenceFeedbackFormTemplate::class,
        'event_feedback_form' => EventFeedbackFormTemplate::class,
        'post_purchase_feedback_form' => PostPurchaseFeedbackFormTemplate::class,
        'website_experience_feedback_form' => WebsiteExperienceFeedbackFormTemplate::class,
        'customer_support_feedback_form' => CustomerSupportFeedbackFormTemplate::class,
        'product_feedback_form' => ProductFeedbackFormTemplate::class,
        'project_completion_feedback_form' => ProjectCompletionFeedbackFormTemplate::class,
        'digital_marketing_internship_application_form' => DigitalMarketingInternshipApplicationFormTemplate::class,
        'software_engineering_internship_application_form'
            => SoftwareEngineeringInternshipApplicationFormTemplate::class,
        'warehouse_logistics_worker_application_form' => WarehouseLogisticsWorkerApplicationFormTemplate::class,
        'marketing_specialist_job_application_form' => MarketingSpecialistJobApplicationFormTemplate::class,
        'software_developer_job_application_form' => SoftwareDeveloperJobApplicationFormTemplate::class,
        'commercial_rental_application_form' => CommercialRentalApplicationFormTemplate::class,
        'student_housing_application_form' => StudentHousingApplicationFormTemplate::class,
        'rental_application_form' => RentalApplicationFormTemplate::class,
        'technical_contractor_application_form' => TechnicalContractorApplicationFormTemplate::class,
        'creative_contractor_application_form' => CreativeContractorApplicationFormTemplate::class,
        'freelancer_application_form' => FreelancerApplicationFormTemplate::class,
        'remote_internship_application_form' => RemoteInternshipApplicationFormTemplate::class,
        'corporate_internship_application_form' => CorporateInternshipApplicationFormTemplate::class,
        'university_internship_application_form' => UniversityInternshipApplicationFormTemplate::class,
        'skilled_trade_employment_application_form' => SkilledTradeEmploymentApplicationFormTemplate::class,
        'retail_employment_application_form' => RetailEmploymentApplicationFormTemplate::class,
        'corporate_employment_application_form' => CorporateEmploymentApplicationFormTemplate::class,
        'skilled_professional_job_application_form' => SkilledProfessionalJobApplicationFormTemplate::class,
        'entry_level_job_application_form' => EntryLevelJobApplicationFormTemplate::class,
        'general_job_application_form' => GeneralJobApplicationFormTemplate::class,
        'office_space_rental_application_form' => OfficeSpaceRentalApplicationFormTemplate::class,
        'apartment_rental_application_form' => ApartmentRentalApplicationFormTemplate::class,
        'ui_ux_designer_freelancer_application_form' => UiUxDesignerFreelancerApplicationFormTemplate::class,
        'video_editor_freelancer_application_form' => VideoEditorFreelancerApplicationFormTemplate::class,
        'custom_event_tshirt_bulk_order_form' => CustomEventTShirtBulkOrderFormTemplate::class,
        'fashion_brand_custom_apparel_order_form' => FashionBrandCustomApparelOrderFormTemplate::class,
        'sports_team_custom_jersey_tshirt_order_form' => SportsTeamCustomJerseyTShirtOrderFormTemplate::class,
        'facility_maintenance_issue_report_form' => FacilityMaintenanceIssueReportFormTemplate::class,
        'it_support_equipment_maintenance_form' => ItSupportEquipmentMaintenanceFormTemplate::class,
        'property_maintenance_repair_request_form' => PropertyMaintenanceRepairRequestFormTemplate::class,
        'corporate_event_sponsorship_application_form' => CorporateEventSponsorshipApplicationFormTemplate::class,
        'startup_sponsorship_application_form' => StartupSponsorshipApplicationFormTemplate::class,
        'media_influencer_sponsorship_application_form' => MediaInfluencerSponsorshipApplicationFormTemplate::class,
        'business_vendor_onboarding_form' => BusinessVendorOnboardingFormTemplate::class,
        'wholesale_supplier_application_form' => WholesaleSupplierApplicationFormTemplate::class,
        'service_provider_partnership_application_form' => ServiceProviderPartnershipApplicationFormTemplate::class,
    ];

    /**
     * Get the template map
     *
     * @return array<string, string>
     */
    public function getTemplateMap(): array
    {
        /**
         * Allows modification of the template mapper.
         *
         * @since 0.1.0
         *
         * @param array<string, string> $templateMaper The current template mapper.
         *
         * @return array<string, string> The modified template mapper.
         */
        self::$templateMap = apply_filters(
            'ivyforms/template/template_mapper',
            self::$templateMap
        );

        return self::$templateMap;
    }

    /**
     * Get template class by template ID
     *
     * @param  string $templateId
     * @return string|null Template class name or null if not found
     */
    public static function getTemplateClass(string $templateId): ?string
    {
        return (new TemplateMapper())->getTemplateMap()[$templateId] ?? null;
    }

    /**
     * Get template data by template ID
     *
     * @param  string $templateId
     * @return array<string, mixed>|null Template data or null if not found
     */
    public static function getTemplate(string $templateId): ?array
    {
        $templateClass = self::getTemplateClass($templateId);

        if (!$templateClass || !class_exists($templateClass)) {
            return null;
        }

        return $templateClass::getTemplate();
    }

    /**
     * Get all template IDs
     *
     * @return array<int, string>
     */
    public static function getAllTemplateIds(): array
    {
        return array_keys((new TemplateMapper())->getTemplateMap());
    }

    /**
     * Check if template ID exists
     *
     * @param  string $templateId
     * @return bool
     */
    public static function hasTemplate(string $templateId): bool
    {
        return self::getTemplateClass($templateId) !== null;
    }

    /**
     * Get all templates metadata (without form_data) for listing purposes
     *
     * Returns only: id, name, description, category, subcategory, is_pro, screenshot
     * This avoids loading heavy form_data (fields, settings) for every template
     * when only metadata is needed for browsing/searching.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getAllTemplateMetas(): array
    {
        $templates = [];

        foreach ((new TemplateMapper())->getTemplateMap() as $templateId => $templateClass) {
            // Skip BlankFormTemplate from public template list
            if ($templateClass === BlankFormTemplate::class) {
                continue;
            }

            if (class_exists($templateClass) && method_exists($templateClass, 'getTemplateMeta')) {
                $templates[$templateId] = $templateClass::getTemplateMeta();
            }
        }

        return $templates;
    }
}
