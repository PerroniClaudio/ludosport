export const getUserMetricValue = (item, displayMode = "active") => {
    if (displayMode === "registered") {
        return item.registered_athletes ?? item.athletes ?? 0;
    }

    return item.active_athletes ?? item.athletes ?? 0;
};

export const sortUserMetricData = (items, displayMode = "active") => {
    return [...items].sort((left, right) => {
        const metricDifference =
            getUserMetricValue(right, displayMode) -
            getUserMetricValue(left, displayMode);

        if (metricDifference !== 0) {
            return metricDifference;
        }

        return left.name.localeCompare(right.name);
    });
};
